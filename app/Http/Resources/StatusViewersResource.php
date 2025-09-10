<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusViewersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return[
            'username' => $this->profile->username,
            'image' => $this->profile->image_url,
            'viewed_at' => $this->pivot->created_at
        ];
    }
}
