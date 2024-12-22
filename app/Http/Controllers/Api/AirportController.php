<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AirportRequest;
use App\Http\Resources\Api\AirportResource;
use App\Models\Airport;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class AirportController extends Controller
{
    public function __construct(private readonly Airport $airportModel) {}

    public function index(): JsonResponse|JsonResource
    {
        try {
            $airports = $this->airportModel::all();

            return ResponseHelper::okResponse(data: AirportResource::collection($airports));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function show($id): JsonResponse|JsonResource
    {
        try {
            $airport = $this->airportModel::find($id);
            if (empty($airport)) {
                return ResponseHelper::notFoundResponse();
            }

            return ResponseHelper::okResponse(data: AirportResource::make($airport));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function store(AirportRequest $request): JsonResponse
    {
        try {
            $airport = $this->airportModel::create($request->validated());

            return ResponseHelper::createdResponse(AirportResource::make($airport));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function update(AirportRequest $request, $id): JsonResponse|JsonResource
    {
        try {
            $airport = $this->airportModel::find($id);
            if (empty($airport)) {
                return ResponseHelper::notFoundResponse();
            }
            $airport->update($request->validated());

            return ResponseHelper::okResponse(data: AirportResource::make($airport));
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }

    public function destroy($id): JsonResponse|JsonResource
    {
        try {
            $airport = $this->airportModel::find($id);
            if (empty($airport)) {
                return ResponseHelper::notFoundResponse();
            }
            $airport->delete();

            return ResponseHelper::okResponse();
        } catch (Exception $exception) {
            return ResponseHelper::internalServerErrorResponse($exception->getMessage());
        }
    }
}
