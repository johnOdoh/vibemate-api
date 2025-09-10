<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'image' => $this->image ? Storage::disk('s3')->url($this->image) : null,
            'comment' => $this->comment,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at->diffForHumans()
        ];
    }
}
