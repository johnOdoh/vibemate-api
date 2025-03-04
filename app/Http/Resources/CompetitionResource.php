<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'hashtag' => $this->hashtag,
            'type' => $this->type,
            'category' => $this->category,
            'prize' => $this->prize,
            'start' => $this->start,
            'end' => $this->end,
            'image' => asset('storage/'.$this->image),
            'user' => [
                'username' => $this->user->username
            ]
        ];
    }
}
