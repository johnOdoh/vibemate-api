<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'email' => $this->email,
            'username' => $this->profile->username,
            'firstname' => $this->profile->firstname,
            'lastname' => $this->profile->lastname,
            'dob' => $this->profile->dob,
            'connect_with' => $this->profile->connect_with,
            'gender' => $this->profile->gender,
            'phone' => $this->profile->phone,
            'here_for' => $this->profile->here_for,
            'bio' => $this->profile->bio,
            'image' => asset('storage/'.$this->profile->image),
            'city' => $this->profile->city,
            'country' => $this->profile->country,
            'interests' => $this->interests->pluck('name')
        ];
    }
}
