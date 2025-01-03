<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Api\CarReservationRequest;
use App\Http\Resources\Api\CarReservationResource;
use App\Http\Resources\Api\CarResource;
use App\Http\Traits\GeneralTrait;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class CarReservationController extends Controller
{
    use GeneralTrait;
    /**
     * @throws ValidationException
     */
    public function getAvailableCars(CarReservationRequest $request): JsonResponse|JsonResource
    {
        $data = $request->validated();

        $totalPassengers = $data['adults'] + $data['childrens'];


        $availableCars = Car::where('type', $data['car_type'])
        ->where('passenger_from', '<=', $totalPassengers)
        ->where('passenger_to', '>=', $totalPassengers)
        ->where('package_to', '>=', $data['packages'])
        ->first();

        if (!$availableCars || $availableCars->isEmpty()) {
//            throw ValidationException::withMessages([
//                'quantity' => __('messages.no_available_cars'),
//            ]);
            return $this->ReturnError('Error',__('messages.no_available_cars'));
        }

        return ResponseHelper::okResponse(data: CarResource::collection($availableCars));
    }
}
