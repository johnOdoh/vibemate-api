<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'has_badge' => 'boolean',
            'is_subscribed' => 'boolean',
            'is_social' => 'boolean'
        ];
    }

    // public function getLocation()
    // {
    //     $location = $this->profile->location;
    //     if ($location) {
    //         return [
    //             'lon' => $location['longitude'],
    //             'lat' => $location['latitude']
    //         ];
    //     }
    //     return ['lon' => 0, 'lat' => 0];
    // }

    public function scopeGetDistance(Builder $query, float $lat, float $lng, int $radius = 100, string $unit = 'miles')
    {
        $earthRadius = $unit === 'km' ? 6371 : 3959;
        return $query
            ->select('users.*') // keep base user columns
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            // ->whereNotNull('profiles.location')
            // ->whereRaw("JSON_EXTRACT(profiles.location, '$.latitude') IS NOT NULL")
            // ->whereRaw("JSON_EXTRACT(profiles.location, '$.longitude') IS NOT NULL")
            ->selectRaw("
                $earthRadius * acos(
                    cos(radians(?))
                    * cos(radians(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(profiles.location, '$.latitude')), 0)))
                    * cos(radians(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(profiles.location, '$.longitude')), 0)) - radians(?))
                    + sin(radians(?))
                    * sin(radians(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(profiles.location, '$.latitude')), 0)))
                ) AS distance
            ", [$lat, $lng, $lat]);
    }


    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function reward()
    {
        return $this->hasOne(Reward::class);
    }

    public function discovery()
    {
        return $this->hasOne(Discovery::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function verification()
    {
        return $this->hasOne(Verification::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class)->latest();
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->latest();
    }

    public function livestreams()
    {
        return $this->hasMany(Livestream::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'following_id', 'follower_id')->withPivot('is_active')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'follower_id', 'following_id')->withPivot('is_active')->withTimestamps();
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'matches', 'sender_id', 'receiver_id')->withPivot('is_matched');
    }

    public function liked_by()
    {
        return $this->belongsToMany(User::class, 'matches', 'receiver_id', 'sender_id')->withPivot('is_matched');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interest');
    }

    public function referrals()
    {
        return $this->belongsToMany(User::class, 'affiliates', 'referrer_id', 'referee_id')->withTimestamps()->withPivot('status');
    }

    public function referredBy()
    {
        return $this->belongsToMany(User::class, 'affiliates', 'referee_id', 'referrer_id');
    }
}
