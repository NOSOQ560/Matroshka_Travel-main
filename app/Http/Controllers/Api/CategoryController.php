<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryController extends Controller
{
    public function __construct(private readonly Category $model) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $categories = $this->model::get();

            return ResponseHelper::okResponse(data: CategoryResource::collection($categories));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $category = $this->model::find($id);
            if (empty($category)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: CategoryResource::make($category));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->model::create($request->validated());

            return ResponseHelper::createdResponse(CategoryResource::make($category));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = $this->model::find($id);
            if (empty($category)) {
                return ResponseHelper::notFoundResponse();
            }
            $category->update($request->validated());

            return ResponseHelper::okResponse(data: CategoryResource::make($category));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $category = $this->model::find($id);
            if (empty($category)) {
                return ResponseHelper::notFoundResponse();
            }
            $category->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
