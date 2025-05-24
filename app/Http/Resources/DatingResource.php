<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatingResource extends JsonResource
{
    protected $my_distance;

    public function __construct($resource, $my_distance)
    {
        parent::__construct($resource);
        $this->my_distance = $my_distance;
    }
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
            'distance' => formatNumber(getDistanceInMiles($this->my_distance, getLocation($this->profile->city, $this->profile->country), 'miles')). ' miles away',
            'bio' => $this->profile->bio,

        ];
    }
}
