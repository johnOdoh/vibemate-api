<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'competition_id' => $this->competition_id,
            'file_url' => asset('storage/'.$this->url),
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username,
                'image' => asset('storage/'.$this->user->profile->image),
            ],
            'no_of_likes' => formatNumber($this->likes()->count()),
            'no_of_comments' => formatNumber($this->comments()->count()),
            'is_liked' => $this->likes()->where('user_id', $request->user()->id)->exists(),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->exists(),
            'created_at' => $this->created_at
        ];
    }
}
