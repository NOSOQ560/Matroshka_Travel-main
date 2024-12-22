<?php

namespace App\Services;

use Illuminate\Support\Str;

class ImageService
{
    private $model;

    private $data;

    public function __construct($model, $data)
    {
        $this->model = $model;
        $this->data = $data;
    }

    /**
     * store Just one media
     */
    public function storeMedia(string $collectionName, string $mainMediaRequest): void
    {
        if (isset($this->data[$mainMediaRequest]) && $mainMediaRequest != null) {
            $this->storeImageFromRequest($this->model,
                $mainMediaRequest,
                $collectionName);
        }
    }

    /**
     * Update just one media, this function delete then add the new media
     */
    public function updateMedia(string $collectionName, string $mainMediaRequest): void
    {
        if (isset($this->data[$mainMediaRequest]) && $mainMediaRequest != null) {
            $this->model->registerMediaCollections();

            self::storeMedia($collectionName, $mainMediaRequest);
        }
    }

    public function updateMedias(
        string $collectionName,
        string $deleteMediasRequest = '',
        string $otherMediasRequest = '',
        string $otherMediasRelationName = 'otherImages'
    ): void {
        self::deleteMediasWithIds($deleteMediasRequest, $otherMediasRelationName);

        self::addOtherMedias($collectionName, $otherMediasRequest);
    }

    /**
     * store many medias
     */
    public function addOtherMedias(string $collectionName, string $otherMediasRequest): void
    {
        if (isset($this->data[$otherMediasRequest]) && $otherMediasRequest != null) {
            foreach ($this->data[$otherMediasRequest] as $image) {
                $this->model->addMedia($image)->toMediaCollection($collectionName,
                );
            }
        }
    }

    public function deleteMediasWithIds(string $deleteMediasRequest, string $otherMediasRelationName = 'otherImages'): void
    {
        info($deleteMediasRequest);
        if (isset($this->data[$deleteMediasRequest]) && $deleteMediasRequest != null) {
            $deletedMedias = array_unique($this->data[$deleteMediasRequest]);

            $this->model
                ->$otherMediasRelationName()
                ->whereIntegerInRaw('id', $deletedMedias)
                ->get()
                ->map(function ($item) {
                    return $item->delete();
                });
        }
    }

    /**
     * Store Image From Request
     */
    public function storeImageFromRequest(
        object $class,
        string $fileName = 'image',
        string $collectionName = 'default',
        ?string $storedFileName = null
    ): object {
        return json_decode($class
            ->addMediaFromRequest($fileName)
            ->usingFileName($storedFileName ?: Str::random().'.'.request()->file($fileName)->extension())
            ->toMediaCollection($collectionName));
    }
}
