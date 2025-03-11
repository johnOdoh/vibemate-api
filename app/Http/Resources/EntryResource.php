<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
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
            'no_of_engagements' => $this->no_of_engagements,
            'body' => $this->body,
            'file_url' => $this->url,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->username
            ],
            'created_at' => $this->created_at,
            'competition' => new CompetitionResource($this->competition),
            'comments' => CommentResource::collection($this->comments)
        ];
    }
}
