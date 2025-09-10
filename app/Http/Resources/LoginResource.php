<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'user' => new UserResource($this),
            'profile' => $this->profile()->exists() ? [
                'id' => $this->profile->id,
                'username' => $this->profile->username,
                'firstname' => $this->profile->firstname,
                'lastname' => $this->profile->lastname,
                'dob' => $this->profile->dob,
                'connect_with' => $this->profile->connect_with,
                'gender' => $this->profile->gender,
                'phone' => $this->profile->phone,
                'here_for' => $this->profile->here_for,
                'bio' => $this->profile->bio,
                'image' => $this->profile->image_url,
                'city' => $this->profile->city,
                'country' => $this->profile->country,
                'interests' => $this->interests->pluck('name'),
            ] : null,
            'token' => $this->token
        ];
    }
}
