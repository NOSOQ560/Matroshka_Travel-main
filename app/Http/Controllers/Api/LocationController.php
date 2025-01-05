<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\locations;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use GeneralTrait;
    public function getLocations(Request $request)
    {
        try {

            $pickupLocations = locations::whereIn('type', ['airport', 'town'])->get();
            $arrivalLocations = locations::whereIn('type', ['town', 'countryside'])->get();

            // إرجاع الأماكن مع النوع المناسب
            return $this->ReturnData('Locations',[
                'pickupLocation' => $pickupLocations,
                'arrivalLocation' => $arrivalLocations,
            ],'done');

        }
        catch (\Exception $ex)
        {
        return $this->ReturnError($ex->getCode(),$ex->getMessage());
        }
    }

}
