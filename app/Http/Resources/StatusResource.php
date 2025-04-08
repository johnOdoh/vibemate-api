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
            "id" => $this->id,
            "firstname" => $this->profile->firstname,
            "lastname" => $this->profile->lastname,
            "statuses" => $this->statuses->map(fn ($status) => [
                "id" => $status->id,
                "content" => $status->type !== 'text' ? asset('storage/'.$status->content) : $status->content,
                "type" => $status->type,
                "created_at" => $status->created_at
            ])
        ];
    }
}
