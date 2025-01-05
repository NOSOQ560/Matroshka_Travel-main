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

//    public function getAvailableCars(CarReservationRequest $request): JsonResponse|JsonResource
//    {
//        $data = $request->validated();
//
//        $totalPassengers = $data['adults'] + $data['childrens'];
//
//        // البحث عن السيارة المتاحة
//        $availableCar = Car::where('type', $data['car_type'])
//            ->where('passenger_from', '<=', $totalPassengers)
//            ->where('passenger_to', '>=', $totalPassengers)
//            ->where('package_to', '>=', $data['packages'])
//            ->first();
//
//        if (!$availableCar) {
//            return $this->ReturnError('Error', __('messages.no_available_cars'));
//        }
//
//        // حساب عدد الساعات بين وقت المغادرة ووقت العودة
//        $departing = strtotime($data['departing']);
//        $returning = strtotime($data['returning']);
//        $durationInSeconds = $returning - $departing;
//
//        // تحويل الفرق إلى ساعات
//        $durationInHours = $durationInSeconds / 3600;
//
//        // حساب التكلفة بناءً على سعر الساعة للسيارة
//        $hourlyRate = $availableCar->hour_in_town; // تأكد من أن الحقل موجود في جدول Car
//        $totalCost = $durationInHours * $hourlyRate;
//
//        // إرجاع البيانات مع التكلفة الإجمالية
//        return $this->ReturnData('availableCar', [
//            'car' => $availableCar,
//            'totalCost' => $totalCost,
//            'durationInHours' => $durationInHours
//        ], 'done');
//    }


    public function getAvailableCars(CarReservationRequest $request): JsonResponse|JsonResource
    {
        // البيانات التي تم التحقق منها
        $data = $request->validated();

        // حساب إجمالي عدد الركاب
        $totalPassengers = $data['adults'] + $data['childrens'];

        // البحث عن السيارة المتاحة بناءً على نوع الرحلة
        $availableCarQuery = Car::where('type', $data['car_type'])
            ->where('passenger_from', '<=', $totalPassengers)
            ->where('passenger_to', '>=', $totalPassengers)
            ->where('package_to', '>=', $data['packages']);

        if ($data['trip_type'] === 'recreational') {
            // إذا كانت الرحلة ترفيهية، تأكد من وجود حقل "returning"
            if (!isset($data['returning'])) {
                return $this->ReturnError('Error', __('messages.returning_required'));
            }

            // تنفيذ البحث عن السيارة
            $availableCar = $availableCarQuery->first();

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
        } elseif ($data['trip_type'] === 'accommodation') {
            // إذا كانت الرحلة "إقامة"، تأكد من وجود الحقول "departure_location" و "arrival_location"
            if (!isset($data['pickup_location']) || !isset($data['arrival_location'])) {
                return $this->ReturnError('Error', __('messages.locations_required'));
            }

            // تحديد نوع الرحلة بناءً على مكان المغادرة والوصول
            $pickupLocation = $data['pickup_location'];
            $arrivalLocation = $data['arrival_location'];

            // البحث عن السيارة المتاحة
            $availableCar = $availableCarQuery->first();

            if (!$availableCar) {
                return $this->ReturnError('Error', __('messages.no_available_cars'));
            }

            // تحديد التكلفة بناءً على مكان المغادرة والوصول
            $locationRate = 0;
            if ($pickupLocation === 'airport' && $arrivalLocation === 'town') {
                $locationRate = $availableCar->airport_to_town;
            } elseif ($pickupLocation === 'town' && $arrivalLocation === 'countryside') {
                $locationRate = $availableCar->town_to_countryside;
            }

            if ($locationRate == 0) {
                return $this->ReturnError('Error', __('messages.invalid_location_pair'));
            }

            // حساب التكلفة بناءً على السيارة المتاحة
            $totalCost = $locationRate; // التكلفة بناءً على السيارة المتاحة فقط

            // إرجاع البيانات مع التكلفة الإجمالية
            return $this->ReturnData('availableCar', [
                'car' => $availableCar,
                'totalCost' => $totalCost,
                'locationPair' => $pickupLocation . '_to_' . $arrivalLocation
            ], 'done');
        }

        return $this->ReturnError('Error', __('messages.invalid_trip_type'));
    }

}
