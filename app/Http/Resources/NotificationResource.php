<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'action' => $this->action,
            'action_id' => $this->action_id,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'comment' => $this->comment,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at->diffForHumans()
        ];
    }
}
