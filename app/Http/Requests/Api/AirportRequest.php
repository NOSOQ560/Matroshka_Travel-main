<?php

namespace App\Http\Requests\Api;

class AirportRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
