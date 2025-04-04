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
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'dob' => $this->dob,
            'connect_with' => $this->connect_with,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'here_for' => $this->here_for,
            'bio' => $this->bio,
            'image' => storage_path('/app/public/'.$this->image),
            'city' => $this->city,
            'country' => $this->country,
            'interests' => $this->user->interests->pluck('name')
        ];
    }
}
