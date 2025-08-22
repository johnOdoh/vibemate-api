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
            'firstname' => $this->profile->firstname,
            'image' => asset('storage/'.$this->profile->image),
            'age' => $this->profile->dob->age,
            'city' => $this->profile->city,
            'country' => $this->profile->country,
            // 'distance' => formatNumber(getDistanceInMiles($request->user()->getLocation(), $this->getLocation(), 'miles')). ' miles away',
            'distance' => formatNumber($this->distance). ' miles away',
            'bio' => $this->profile->bio,
            'height' => $this->discovery ? $this->discovery->height : null,
            'education' => $this->discovery ?  $this->discovery->education : null,
            'working' => $this->discovery ?  $this->discovery->employment_company : null,
            'languages' => $this->discovery ?  explode(', ', $this->discovery->languages) : null,
            'looking_for' => $this->discovery ?  explode(', ', $this->profile->here_for) : null,
            'down_to_meet' => $this->discovery ?  explode(', ', $this->discovery->down_to_meet) : null,
        ];
    }
}
