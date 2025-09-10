<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'file_url' => Storage::disk('s3')->url($this->url),
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username,
                'image' => $this->user->profile->image_url,
            ],
            'no_of_likes' => formatNumber($this->likes()->count()),
            'no_of_comments' => formatNumber($this->comments()->count()),
            'is_liked' => $this->likes->contains('user_id', $request->user()->id),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->where('is_active', true)->exists() || request()->user()->id === $this->user->id,
            'created_at' => $this->created_at
        ];
    }
}
