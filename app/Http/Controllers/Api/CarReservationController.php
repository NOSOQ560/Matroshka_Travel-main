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
//    public function getAvailableCars(CarReservationRequest $request): JsonResponse|JsonResource
//    {
//        $data = $request->validated();
//
//        $totalPassengers = $data['adults'] + $data['childrens'];
//
//
//        $availableCars = Car::where('type', $data['car_type'])
//        ->where('passenger_from', '<=', $totalPassengers)
//        ->where('passenger_to', '>=', $totalPassengers)
//        ->where('package_to', '>=', $data['packages'])
//        ->first();
//
//        if (!$availableCars ) {
////            throw ValidationException::withMessages([
////                'quantity' => __('messages.no_available_cars'),
////            ]);
//            return $this->ReturnError('Error',__('messages.no_available_cars'));
//        }
//
////        return ResponseHelper::okResponse(data: CarResource::collection($availableCars));
//        return $this->ReturnData('availableCars',$availableCars,'done');
//    }

    public function getAvailableCars(CarReservationRequest $request): JsonResponse|JsonResource
    {
        $data = $request->validated();

        $totalPassengers = $data['adults'] + $data['childrens'];

        // البحث عن السيارة المتاحة
        $availableCar = Car::where('type', $data['car_type'])
            ->where('passenger_from', '<=', $totalPassengers)
            ->where('passenger_to', '>=', $totalPassengers)
            ->where('package_to', '>=', $data['packages'])
            ->first();

        if (!$availableCar) {
            return $this->ReturnError('Error', __('messages.no_available_cars'));
        }

        // حساب عدد الساعات بين وقت المغادرة ووقت العودة
        $departing = strtotime($data['departing']);
        $returning = strtotime($data['returning']);
        $durationInSeconds = $returning - $departing;

        // تحويل الفرق إلى ساعات
        $durationInHours = $durationInSeconds / 3600;

        // حساب التكلفة بناءً على سعر الساعة للسيارة
        $hourlyRate = $availableCar->hour_in_town; // تأكد من أن الحقل موجود في جدول Car
        $totalCost = $durationInHours * $hourlyRate;

        // إرجاع البيانات مع التكلفة الإجمالية
        return $this->ReturnData('availableCar', [
            'car' => $availableCar,
            'totalCost' => $totalCost,
            'durationInHours' => $durationInHours
        ], 'done');
    }

}
