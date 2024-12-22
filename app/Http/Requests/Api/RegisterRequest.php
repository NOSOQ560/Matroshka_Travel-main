<?php

namespace App\Http\Requests\Api;

use App\Enums\GenderTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class RegisterRequest extends CustomRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(UserTypeEnum::values())],
            'email' => ['required', 'string', 'email:rfc,dns', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', Rule::unique('users', 'phone')],
            'phone_country' => ['required_with:phone', 'string'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
        if ($this->type == 'company') {
            $rules = array_merge($rules, [
                'website' => ['required', 'string', 'url'],
                'social_media' => ['required', 'string', 'url'],
            ]);
        } else {
            $rules = array_merge($rules, [
                'gender' => ['required', 'string', Rule::in(GenderTypeEnum::values())],
            ]);
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        return array_merge($validated, [
            'country' => $this->phone_country,
        ]);
    }
}
