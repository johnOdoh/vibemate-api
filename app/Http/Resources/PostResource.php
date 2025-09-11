<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    protected bool $single;

    public function __construct($resource, $single = false)
    {
        parent::__construct($resource);
        $this->single = $single;
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
            'privacy' => $this->privacy,
            'allow_comments' => $this->allow_comments,
            'allow_like_counts' => $this->allow_like_counts,
            'no_of_engagements' => formatNumber($this->no_of_engagements),
            'body' => $this->body,
            'hashtags' => $this->hashtags,
            'location' => $this->location,
            'file_url' => $this->file_urls,
            'created_at' => $this->created_at,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username,
                'image' => $this->user->profile->image_url
            ],
            // 'updated_at' => $this->updated_at,
            'likes' => $this->likes->pluck('user_id'),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->where('is_active', true)->exists() || $this->user->id === $request->user()->id,
            'comments_count' => formatNumber($this->comments->count())
            // $this->mergeWhen($this->single, [
            //     'comments' => CommentResource::collection($this->comments),
            // ])
        ];
    }
}
