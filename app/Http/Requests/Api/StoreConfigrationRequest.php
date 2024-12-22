<?php

namespace App\Http\Requests\Api;

use App\Enums\ConfigrationTypeEnum;
use Illuminate\Validation\Rule;

class StoreConfigrationRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'key' => ['sometimes', 'string'],
            'value' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(ConfigrationTypeEnum::values())],
        ];
    }
}
