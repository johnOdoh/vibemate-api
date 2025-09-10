<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReelResource extends JsonResource
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
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username,
                'image' => $this->user->profile->image_url,
            ],
            'body' => $this->body,
            'hashtags' => $this->hashtags,
            'file_url' => $this->video_urls,
            'likes_count' => $this->likes->count(),
            'is_liked' => $this->likes->contains('user_id', $request->user()->id),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->where('is_active', true)->exists() || $request->user()->id === $this->user->id,
            'comments_count' => formatNumber($this->comments->count()),
            'created_at' => $this->created_at,
        ];
    }
}
