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
            'no_of_engagements' => $this->no_of_engagements,
            'file_url' => asset('storage/'.$this->url),
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username
            ],
            'is_image' => $this->is_image,
            'created_at' => $this->created_at,
            // 'competition' => new CompetitionResource($this->competition),
            // 'comments' => CommentResource::collection($this->comments)
        ];
    }
}
