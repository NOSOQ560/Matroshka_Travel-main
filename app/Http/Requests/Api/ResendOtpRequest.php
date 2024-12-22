<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class ResendOtpRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::exists('users', 'email')],
        ];
    }
}
