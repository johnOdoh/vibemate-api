<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\DatingResource;
use App\Http\Resources\ProfileResource;

class UserController extends Controller
{
    public function index()
    {
        // return getLocation('Bernhardhaven', 'British Indian Ocean Territory (Chagos Archipelago');
        $my_location = getLocation(request()->user()->profile->city, request()->user()->profile->country);
        $users = User::where('id', '!=', request()->user()->id)->whereHas('profile')->limit(10)->get();
        return DatingResource::collection(
            $users->map(fn($user) => new DatingResource($user, $my_location))
        );

        $profile = request()->user()->profile;
        $interests = $profile->user->interests->pluck('name');
        $interested_in = $profile->connect_with;
        $gender = $profile->gender;
        $here_for = explode(', ', $profile->here_for);

        $query = User::query();
        $query->whereHas('interests', function ($q) use ($interests) {
            $q->whereIn('name', $interests); //you have at least one interest in common with user
        });
        $query->whereHas('profile', function ($q) use ($interested_in, $here_for, $gender) {
            $q->where(function ($q) use ($here_for) {
                foreach ($here_for as $item) {
                    $q->orWhere('here_for', 'like', "%$item%"); //you have at least one here_for in common with user
                }
            });
            $q->where(function ($q) use ($gender) { //user wants to connect with your specified gender or with everyone
                $q->where('connect_with', $gender)->orWhere('connect_with', 'everyone');
            });
            if($interested_in !== 'everyone') {
                $q->where('gender', $interested_in); //you are interested in user gender or with everyone
            }
        });
        $users = $query->get();
        return ProfileResource::collection($users);
    }

    public function show(Request $request)
    {;
        return new UserResource($request->user());
    }

    public function follow(Request $request, string $id)
    {
        if(!$request->user()->following()->where('following_id', $id)->exists()) {//if user has followed this user before
            $request->user()->following()->attach($id);
            $user = User::find($id);
            $user->most_followed_points++;
            $user->most_popular_points++;
            $user->save();
        }
        else $request->user()->following()->updateExistingPivot($id, ['is_active' => true]);
        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(Request $request, string $id)
    {
        $request->user()->following()->updateExistingPivot($id, ['is_active' => false]);
        return response()->json(['message' => 'You have unfollowed this user.'], 200);
    }

    public function like(Request $request, string $id)
    {
        if(!$request->user()->likes()->where('receiver_id', $id)->exists()) {//if user has not liked this user already
            $user = User::findOrFail($id);
            $user->profile->profile_views++;
            $user->profile->weekly_profile_views++;
            $request->user()->no_of_swipes++;
            if($request->user()->liked_by()->where('sender_id', $id)->exists()) {//if user has been liked by this user already
                //update the pivot table to show that there is a match
                $request->user()->liked_by()->updateExistingPivot($id, ['is_matched' => true]);
                $request->user()->most_matches_points++;
                $request->user()->save();
                $user->most_matches_points++;
                $user->most_popular_points++;
                $user->push();
                return response()->json(['message' => 'You have a match.', 'is_matched' => true], 200);
            }else {
                $request->user()->likes()->attach($id);
                $request->user()->save();
                $user->most_popular_points++;
                $user->push();
                return response()->json(['message' => 'You have liked this user.', 'is_matched' => false], 200);
            }
        }else {
            return response()->json(['message' => 'You have already liked this user.'], 403);
        }
    }

    public function unlike(Request $request, string $id)
    {
        $request->user()->likes()->where('receiver_id', $id)->exists() ? $request->user()->likes()->detach($id) : $request->user()->liked_by()->detach($id);
        return response()->json(['message' => 'You have unliked this user.'], 200);
    }

    public function getLiked(Request $request)
    {
        return $request->user()->likes()->orderByDesc('id')->get()->map(fn ($user) => [
                'id' => $user->id,
                'firstname' => $user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'image' => asset('storage/' . $user->profile->image),
                'age' => $user->profile->dob->age,
                'city' => $user->profile->city,
                'country' => $user->profile->country,
            ]);
    }

    public function getLikedBy(Request $request)
    {
        return $request->user()->liked_by()->orderByDesc('id')->get()->map(fn ($user) => [
                'id' => $user->id,
                'firstname' => $user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'image' => asset('storage/' . $user->profile->image),
                'age' => $user->profile->dob->age,
                'city' => $user->profile->city,
                'country' => $user->profile->country,
            ]);
    }

    public function favoriteUser(Request $request, string $id)
    {
        $favorites = empty($request->user()->profile->favorites) ? [] : explode(',', $request->user()->profile->favorites);
        if(!in_array($id, $favorites)) {
            $request->user()->profile->update(['favorites' => implode(',', array_merge($favorites, [$id]))]);
        }
        $user = User::findOrFail($id);
        $user->profile->profile_views++;
        $user->profile->weekly_profile_views++;
        $user->profile->save();
        $request->user()->no_of_swipes++;
        $request->user()->save();
        return response()->json(['message' => 'Profile Saved.'], 200);
    }

    public function unfavoriteUser(Request $request, string $id)
    {
        $favorites = empty($request->user()->profile->favorites) ? [] : explode(',', $request->user()->profile->favorites);
        if(in_array($id, $favorites)) {
            $request->user()->profile->update(['favorites' => implode(',', array_diff($favorites, [$id]))]);
        }
        return response()->json(['message' => 'Profile Unsaved.'], 200);
    }

    public function getFavorites(Request $request)
    {
        $favorites = empty($request->user()->profile->favorites) ? [] : explode(',', $request->user()->profile->favorites);
        return User::whereIn('id', $favorites)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'firstname' => $user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'image' => asset('storage/' . $user->profile->image),
                'age' => $user->profile->dob->age,
                'city' => $user->profile->city,
                'country' => $user->profile->country,
            ]);
    }

    public function discardUser(Request $request, string $id)
    {
        $discarded = empty($request->user()->profile->discarded) ? [] : explode(',', $request->user()->profile->discarded);
        if(!in_array($id, $discarded)) {
            $request->user()->profile->update(['discarded' => implode(',', array_merge($discarded, [$id]))]);
        }
        $user = User::findOrFail($id);
        $user->profile->profile_views++;
        $user->profile->weekly_profile_views++;
        $user->profile->save();
        $request->user()->no_of_swipes++;
        $request->user()->save();
        return response()->json(['message' => 'Profile Discarded.'], 200);
    }

    public function unDiscardUser(Request $request, string $id)
    {
        $discarded = empty($request->user()->profile->discarded) ? [] : explode(',', $request->user()->profile->discarded);
        if(in_array($id, $discarded)) {
            $request->user()->profile->update(['discarded' => implode(',', array_diff($discarded, [$id]))]);
        }
        return response()->json(['message' => 'Profile Undiscarded.'], 200);
    }

    public function swipe(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->profile->profile_views++;
        $user->profile->weekly_profile_views++;
        $user->profile->save();
        $request->user()->no_of_swipes++;
        $request->user()->save();
        return response()->json(['message' => 'Profile swiped.'], 200);
    }

    public function getAffiliatePage(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'referral_count' => $user->referral_count,
            'amount_earned' => $user->referral_amount_earned,
            'amount_withdrawn' => $user->referral_amount_withdrawn,
            'amount_available' => number_format($user->referral_amount_earned - $user->referral_amount_withdrawn, 2),
            'counter' => $user->referral_count % 7,
            'referrals' => $user->referrals()->whereHas('profile')->orderByDesc('id')->limit(5)->get()->map(fn($referral) => [
                'firstname' => $referral->profile->firstname,
                'lastname' => $referral->profile->lastname,
                'image' => asset('storage/' . $referral->profile->image),
                'created_at' => $referral->created_at->diffForHumans()
            ]),
        ]);
    }

    public function getUserAnalytics(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        $matches = DB::table('matches')
            ->select('sender_id', DB::raw('COUNT(*) as total_matches'))
            ->where('is_matched', true)
            ->groupBy('sender_id')
            ->orderByDesc('total_matches')
            ->limit(2)
            ->get();
        $matches = $matches->map(function ($match) {
            $user = User::find($match->sender_id);
            $total_likes = Db::table('matches')
                ->where('sender_id', $match->sender_id)
                ->count();
            return [
                'id' => $user->id,
                'firstname' => $user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'image' => asset('storage/' . $user->profile->image),
                'match_percentage' => round($match->total_matches / $total_likes * 100).'%', 
            ];
        })->sortBy('match_percentage')->values();
        
        return response()->json([
            'post_engagements' => $request->user()->posts()->sum('no_of_engagements'),
            'matches' => $request->user()->likes()->where('is_matched', true)->count() + $request->user()->liked_by()->where('is_matched', true)->count(),
            'profile_views' => $request->user()->profile->profile_views,
            'weekly_profile_views' => $request->user()->profile->weekly_profile_views,
            'weekly_post_engagements' => $request->user()->posts()->sum('most_engagements_points'),
            'weekly_likes_received' => $request->user()->posts()
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->withCount('likes')
                ->get()
                ->sum('likes_count'),
            'top_matches' => $matches
        ]);
    }

    public function transferToken(Request $request, User $user)
    {
        if ($request->user()->wallet < $request->amount) {
            return response()->json(['message' => 'Insufficient token balance.'], 403);
        }
        $request->user()->wallet -= $request->amount;
        $user->wallet += $request->amount;
        $request->user()->save();
        $user->save();
        return response()->json(['message' => 'Token transfer successful.'], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }
}
