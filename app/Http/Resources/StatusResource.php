<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
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
            'firstname' => $this->profile->firstname,
            'lastname' => $this->profile->lastname,
            'image' => asset('storage/'.$this->profile->image),
            'statuses' => $this->statuses->map(fn ($status) => [
                'id' => $status->id,
                'content' => $status->content ? asset('storage/'.$status->content) : null,
                'caption' => $status->caption,
                'color' => $status->color,
                'music_url' => $status->music_url,
                'type' => $status->type,
                'created_at' => $status->created_at
            ])
        ];
    }
}
