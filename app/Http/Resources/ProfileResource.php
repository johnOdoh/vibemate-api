<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    protected bool $showExtra;

    public function __construct($resource, $showExtra = false)
    {
        parent::__construct($resource);
        $this->showExtra = $showExtra;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
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
            'image' => asset('storage/'.$this->image),
            'city' => $this->city,
            'country' => $this->country,
            'interests' => $this->user->interests->pluck('name'),
            $this->mergeWhen($this->showExtra, [
                'posts' => $this->user->posts->count(),
                'followers' => $this->user->followers->count(),
                'following' => $this->user->following->count(),
                'likes' => $this->user->liked_by->count(),
            ]),
        ];
    }
}
