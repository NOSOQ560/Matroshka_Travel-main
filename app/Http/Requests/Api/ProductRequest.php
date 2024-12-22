<?php

namespace App\Http\Requests\Api;

class ProductRequest extends CustomRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['sometimes', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer'],
            'main_image' => ['required', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif', 'max:10240'],
            'other_images' => ['sometimes', 'array'],
            'other_images.*' => ['sometimes', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif', 'max:10240'],
            'delete_images' => ['sometimes', 'array'],
            'delete_images.*' => ['sometimes', 'integer'],
        ];
    }
}
