<?php

namespace App\Http\Resources;

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\FocusOn;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Resize;
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
        if($this->is_image) {
            $cloudinary = new Cloudinary();
            $publicId = str_replace("https://res.cloudinary.com/dpn0v6a2i/image/upload/", "", $this->url);
            $url = $cloudinary->image($publicId)->resize(Resize::fill(200, 200)->gravity(Gravity::auto()))->toUrl();
            // $url = $cloudinary->image($publicId)->resize(Resize::fill(200, 200)->gravity(Gravity::focusOn(FocusOn::FACE)))->toUrl();
        }
        else $url = $this->url;
        return [
            'id' => $this->id,
            'no_of_engagements' => $this->no_of_engagements,
            'file_url' => $url,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->username
            ],
            'is_image' => $this->is_image,
            'created_at' => $this->created_at,
            'competition' => new CompetitionResource($this->competition),
            'comments' => CommentResource::collection($this->comments)
        ];
    }
}
