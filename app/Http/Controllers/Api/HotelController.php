<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HotelRequest;
use App\Http\Resources\Api\HotelResource;
use App\Models\Hotel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelController extends Controller
{
    private HotelResource $resource;

    public function __construct(private readonly Hotel $model)
    {
        $this->resource = new HotelResource(null);
    }

    public function index(): JsonResponse|JsonResource
    {
        try {
            $data = $this->model::all();

            return ResponseHelper::okResponse(data: $this->resource::collection($data));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id): JsonResponse|JsonResource
    {
        try {
            $data = $this->model::find($id);
            if (empty($data)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: $this->resource::make($data));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(HotelRequest $request): JsonResponse
    {
        try {
            $data = $this->model::create($request->validated());

            return ResponseHelper::createdResponse($this->resource::make($data));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(HotelRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $data = $this->model::find($id);
            if (empty($data)) {
                return ResponseHelper::notFoundResponse();
            }
            $data->update($request->validated());

            return ResponseHelper::okResponse(data: $this->resource::make($data));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        try {
            $data = $this->model::find($id);
            if (empty($data)) {
                return ResponseHelper::notFoundResponse();
            }
            $data->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
