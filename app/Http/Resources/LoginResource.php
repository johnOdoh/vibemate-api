<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => (string)$this->id,
                'email' => $this->email,
                'wallet' => $this->wallet,
                'is_verified' => $this->is_verified,
                'is_subscribed' => $this->is_subscribed,
                'has_badge' => $this->has_badge,
                'status' => $this->status,
            ],
            'token' => $this->token,
            'has_profile' => $this->profile()->exists()
        ];
    }
}
