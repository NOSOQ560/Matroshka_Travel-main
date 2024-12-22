<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class CarRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'brand' => ['required', 'string'],
            'type' => ['required', Rule::in(['normal', 'business'])],
            'passenger_from' => ['required', 'integer'],
            'passenger_to' => ['required', 'integer'],
            'package_from' => ['required', 'integer'],
            'package_to' => ['sometimes', 'integer'],
            'airport_to_town' => ['required', 'numeric'],
            'hour_in_town' => ['required', 'numeric'],
        ];
    }
}
