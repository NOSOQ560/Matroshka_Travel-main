<?php

namespace App\Http\Requests\Api;

class LoginRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }
}
