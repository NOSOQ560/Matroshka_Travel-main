<?php

namespace App\Http\Requests\Api;

class HotelRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
