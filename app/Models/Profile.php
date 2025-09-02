<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'datetime',
        'location' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['firstname'] = ucwords(strtolower(trim($value)));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['lastname'] = ucwords(strtolower(trim($value)));
    }

    public function visited()
    {
        return $this->belongsToMany(Profile::class, 'visitors', 'visitor_id', 'visited_id')
            ->withPivot('count')
            ->withTimestamps();
    }

    public function visitors()
    {
        return $this->belongsToMany(Profile::class, 'visitors', 'visited_id', 'visitor_id')
            ->withPivot('count');
    }
}
