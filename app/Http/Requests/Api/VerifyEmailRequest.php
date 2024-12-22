<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class VerifyEmailRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', Rule::exists('users', 'email')],
            'otp' => ['required', 'string'],
        ];
    }
}
