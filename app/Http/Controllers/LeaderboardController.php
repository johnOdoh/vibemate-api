<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

class LeaderboardController extends Controller
{
    public function getMostPopular()
    {
        return User::whereHas('profile')->orderBy('most_popular_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_popular_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);
    }

    public function getMostFollowed()
    {
        return User::whereHas('profile')->orderBy('most_followed_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_followed_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);
    }

    public function getMostMatches()
    {
        return User::whereHas('profile')->orderBy('most_matches_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_matches_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);
    }

    public function getMostEngaged()
    {
        return Post::whereRelation('user', 'id', '!=', 0)->orderBy('most_engagements_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->user->id,
                'post_id' => $post->id,
                'firstname' =>$post->user->profile->firstname ?? '',
                'lastname' => $post->user->profile->lastname ?? '',
                'points' => $post->most_engagements_points,
                'followers_count' => formatNumber($post->user->followers()->where('is_active', true)->count())
            ]);
    }
}
