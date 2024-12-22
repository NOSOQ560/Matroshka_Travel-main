<?php

namespace App\Http\Requests\Api;

use App\Enums\GenderTypeEnum;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateProfileRequest extends CustomRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string'],
            'phone' => ['sometimes', new Phone, Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'phone_country' => ['required_with:phone', 'string'],
        ];
        if ($this->user()->type === 'company') {
            $rules = array_merge($rules, [
                'website' => ['sometimes', 'string', 'url'],
                'social_media' => ['sometimes', 'string', 'url'],
            ]);
        } else {
            $rules = array_merge($rules, [
                'gender' => ['sometimes', 'string', Rule::in(GenderTypeEnum::values())],
            ]);
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        return array_merge($validated, [
            'country' => $this->phone_country ?? $this->user()->country,
        ]);
    }
}
