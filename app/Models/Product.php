<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'stock'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product')->singleFile();
    }

    public function mainImage(): MorphMany
    {
        return $this
            ->media()
            ->where('collection_name', 'product')
            ->select(['id', 'model_id', 'disk', 'file_name', 'mime_type']);
    }

    public function otherImages(): MorphMany
    {
        return $this
            ->media()
            ->where('collection_name', 'product-other-images')
            ->select(['id', 'model_id', 'disk', 'file_name', 'mime_type']);
    }
}
