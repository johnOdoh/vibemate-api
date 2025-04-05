<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'privacy' => $this->privacy,
            'allow_comments' => $this->allow_comments,
            'allow_like_counts' => $this->allow_like_counts,
            'no_of_engagements' => $this->no_of_engagements,
            'body' => $this->body,
            'hashtags' => $this->hashtags,
            'location' => $this->location,
            'file_url' => $this->file_url ? array_map(fn($file) => asset('storage/' . $file), explode(', ', $this->file_url))  : [],
            'meta_location' => $this->meta_location,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile ? $this->user->profile->username : null,
            ],
            'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'likes' => [
                'users' => $this->likes->map(fn($like) => [
                    'id' => $like->user->id,
                    'username' => $like->user->profile ? $like->user->profile->username : null,
                ]),
                'count' => $this->likes()->count()
            ],
            'comments' => CommentResource::collection($this->comments)
        ];
    }
}
