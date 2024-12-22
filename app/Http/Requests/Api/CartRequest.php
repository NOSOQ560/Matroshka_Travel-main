<?php

namespace App\Http\Requests\Api;

class CartRequest extends CustomRequest
{
    public function rules(): array
    {
        $rules = [
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
        ];
        if ($this->method() == 'PATCH') {
            unset($rules['product_id']);
        }

        return $rules;
    }
}
