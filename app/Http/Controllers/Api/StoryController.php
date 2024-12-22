<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStoryRequest;
use App\Http\Requests\Api\UpdateStoryRequest;
use App\Http\Resources\Api\StoryResource;
use App\Models\Story;
use App\Services\ImageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryController extends Controller
{
    public function __construct(private readonly Story $storymodel) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $stories = $this->storymodel::with('media')->get();

            return ResponseHelper::okResponse(data: StoryResource::collection($stories));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id): JsonResponse|JsonResource
    {
        try {
            $story = $this->storymodel::with('media')->find($id);
            if (empty($story)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: StoryResource::make($story));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(StoreStoryRequest $request)
    {
        try {
            $data = $request->validated();
            $story = $this->storymodel::create($data);
            $imageService = (new ImageService($story, $data));
            $imageService->storeMedia('story', 'file');

            return ResponseHelper::createdResponse(StoryResource::make($story));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(UpdateStoryRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $data = $request->validated();
            $story = $this->storymodel::find($id);
            if (empty($story)) {
                return ResponseHelper::notFoundResponse();
            }
            $story->update($data);
            $imageService = (new ImageService($story, $data));
            $imageService->updateMedia('story', 'file');

            return ResponseHelper::okResponse(data: StoryResource::make($story));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $story = $this->storymodel::find($id);
            if (empty($story)) {
                return ResponseHelper::notFoundResponse();
            }
            $story->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
