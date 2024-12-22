<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }
}
