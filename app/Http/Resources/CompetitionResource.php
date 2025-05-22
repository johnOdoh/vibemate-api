<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'hashtag' => $this->hashtag,
            'type' => $this->type,
            'category' => $this->category,
            'prize' => $this->prize,
            'is_multiple' => $this->is_multiple,
            'prize_1' => $this->prize_1,
            'prize_2' => $this->prize_2,
            'prize_3' => $this->prize_3,
            'status' => $this->status,
            'phase' => $this->phase,
            'start' => $this->start,
            'end' => $this->end,
            'image' => asset('storage/'.$this->image),
            'user' => [
                'username' => $this->user->profile->username
            ]
        ];
    }
}
