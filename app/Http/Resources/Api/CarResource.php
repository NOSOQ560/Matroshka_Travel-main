<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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
            'name' => $this->whenHas('name'),
            'brand' => $this->whenHas('brand'),
            'type' => $this->whenHas('type'),
            'passenger_from' => $this->whenHas('passenger_from'),
            'passenger_to' => $this->whenHas('passenger_to'),
            'package_from' => $this->whenHas('package_from'),
            'package_to' => $this->whenHas('package_to'),
            'airport_to_town' => $this->whenHas('airport_to_town'),
            'hour_in_town' => $this->whenHas('hour_in_town'),
        ];
    }
}
