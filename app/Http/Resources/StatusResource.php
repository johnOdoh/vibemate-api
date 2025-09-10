<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'image' => $this->profile->image_url,
            'statuses' => $this->statuses->map(fn ($status) => [
                'id' => $status->id,
                'content' => $status->content ? Storage::disk('s3')->url($status->content) : null,
                'caption' => $status->caption,
                'color' => $status->color,
                'music_url' => $status->music_url,
                'type' => $status->type,
                'created_at' => $status->created_at
            ])
        ];
    }
}
