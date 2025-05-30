<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use function Laravel\Prompts\form;

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
            'id' => $this->id,
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
                'posts_count' => formatNumber($this->user->posts->count()),
                // 'followers' => $this->user->followers()->where('is_active', true)->get()->map(fn($follower) => [
                //     'id' => $follower->id,
                // ]),
                'followers_count' => formatNumber($this->user->followers()->where('is_active', true)->count()),
                'following_count' => formatNumber($this->user->following()->where('is_active', true)->count()),
                'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->where('is_active', true)->exists() || $request->user()->id === $this->user->id,
                'likes_count' => formatNumber($this->user->liked_by->count()),
                'posts' => PostResource::collection($this->user->posts)
            ]),
        ];
    }
}
