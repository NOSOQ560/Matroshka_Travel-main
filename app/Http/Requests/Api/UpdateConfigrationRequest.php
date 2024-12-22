<?php

namespace App\Http\Requests\Api;

use App\Enums\ConfigrationTypeEnum;
use Illuminate\Validation\Rule;

class UpdateConfigrationRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'key' => ['sometimes', 'string'],
            'value' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string', Rule::in(ConfigrationTypeEnum::values())],
        ];
    }
}
