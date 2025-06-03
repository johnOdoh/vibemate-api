<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => (string)$this->id,
            'email' => $this->email,
            'wallet' => $this->wallet,
            'is_verified' => $this->is_verified,
            'is_subscribed' => $this->is_subscribed,
            'badge_status' => $this->badge_status,
            'status' => $this->status,
        ];
    }
}
