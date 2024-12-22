<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarRequest;
use App\Http\Resources\Api\CarResource;
use App\Models\Car;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class CarController extends Controller
{
    public function __construct(private readonly Car $carModel) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $cars = $this->carModel::all();

            return ResponseHelper::okResponse(data: CarResource::collection($cars));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id): JsonResponse|JsonResource
    {
        try {
            $car = $this->carModel::find($id);
            if (empty($car)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: CarResource::make($car));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(CarRequest $request): JsonResponse
    {
        try {
            $car = $this->carModel::create($request->validated());

            return ResponseHelper::createdResponse(CarResource::make($car));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(CarRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $car = $this->carModel::find($id);
            if (empty($car)) {
                return ResponseHelper::notFoundResponse();
            }
            $car->update($request->validated());

            return ResponseHelper::okResponse(data: CarResource::make($car));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        try {
            $car = $this->carModel::find($id);
            if (empty($car)) {
                return ResponseHelper::notFoundResponse();
            }
            $car->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
