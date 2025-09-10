<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatingResource extends JsonResource
{
    // protected $my_distance;

    // public function __construct($resource, $my_distance)
    // {
    //     parent::__construct($resource);
    //     $this->my_distance = $my_distance;
    // }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profile_id' => $this->profile->id,
            'firstname' => $this->profile->firstname,
            'image' => $this->profile->image_url,
            'age' => $this->profile->dob->age,
            'city' => $this->profile->city,
            'country' => $this->profile->country,
            // 'distance' => formatNumber(getDistanceInMiles($request->user()->getLocation(), $this->getLocation(), 'miles')). ' miles away',
            'distance' => formatNumber($this->distance). ' miles away',
            'bio' => $this->profile->bio,
            'height' => $this->discovery?->height,
            'education' => $this->discovery?->education,
            'working' => $this->discovery?->employment_company,
            'languages' => $this->discovery ?  explode(', ', $this->discovery->languages) : null,
            'looking_for' => $this->discovery ?  explode(', ', $this->profile->here_for) : null,
            'down_to_meet' => $this->discovery ?  explode(', ', $this->discovery->down_to_meet) : null,
        ];
    }
}
