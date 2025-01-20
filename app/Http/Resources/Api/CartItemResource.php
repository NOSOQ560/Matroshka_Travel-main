<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => ProductResource::make($this->whenLoaded('product')),
            'quantity' => $this->whenHas('quantity'),
            'total' => $this->quantity * $this->product->price,
            'image_url' => $this->product->mainImage->first() ? $this->product->mainImage->first()->original_url : null, // استخدام first() للحصول على الصورة الأولى

        ];
    }
}
