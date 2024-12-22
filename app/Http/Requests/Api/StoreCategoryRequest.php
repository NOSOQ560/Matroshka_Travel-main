<?php

namespace App\Http\Requests\Api;

class StoreCategoryRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }
}
