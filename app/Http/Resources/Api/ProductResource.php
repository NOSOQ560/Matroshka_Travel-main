<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->when(! $this->relationLoaded('category') && ! is_null('category_id'), $this->category_id),
            'name' => $this->whenHas('name'),
            'description' => $this->whenHas('description'),
            'price' => $this->whenHas('price', number_format($this->price, 2)),
            'stock' => $this->whenHas('stock'),
            'main_image' => $this->when($this->relationLoaded('mainImage'), $this->mainImage->first()->original_url ?: null),
            'other_images' => $this->when($this->relationLoaded('otherImages'), $this->otherImages->map(
                function ($file) {
                    return ['id' => $file->id, 'url' => $file->original_url];
                }
            )),
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
