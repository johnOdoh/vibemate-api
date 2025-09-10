<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'type' => $this->type,
            'category' => $this->category,
            'prize' => $this->prize,
            'no_of_participants' => formatNumber($this->entries()->count()),
            // 'status' => $this->status,
            'phase' => $this->phase,
            'start' => $this->start->diffForHumans(),
            'end' => $this->end->diffForHumans(),
            'is_featured' => $this->is_featured,
            'is_multiple' => $this->is_multiple,
            'hashtag' => $this->hashtag,
            'prize_1' => $this->prize_1,
            'prize_2' => $this->prize_2,
            'prize_3' => $this->prize_3,
            'image' => Storage::disk('s3')->url($this->image),
            'user' => [
                'firstname' => $this->user->profile->firstname,
                'lastname' => $this->user->profile->lastname,
                'image' => $this->user->profile->image_url
            ]
        ];
    }
}
