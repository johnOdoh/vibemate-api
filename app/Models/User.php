<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
            'is_verified' => 'boolean'
        ];
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'follower_id', 'following_id');
    }

    public function swipes()
    {
        return $this->belongsToMany(User::class, 'matches', 'sender_id', 'receiver_id')->withPivot('is_accepted');
    }

    public function requests()
    {
        return $this->belongsToMany(User::class, 'matches', 'receiver_id', 'sender_id')->withPivot('is_accepted');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interest');
    }

    public function referrals()
    {
        return $this->belongsToMany(User::class, 'affiliates', 'referrer_id', 'referee_id');
    }

    public function referredBy()
    {
        return $this->belongsToMany(User::class, 'affiliates', 'referee_id', 'referrer_id');
    }
}
