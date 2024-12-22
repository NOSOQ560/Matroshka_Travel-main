<?php

namespace App\Http\Requests\Api;

class UpdateCategoryRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
        ];
    }
}
