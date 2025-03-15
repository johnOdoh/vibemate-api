<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;

class UserController extends Controller
{
    public function index()
    {
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

    public function follow(Request $request, string $id)
    {
        $request->user()->following()->attach($id);
        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(Request $request, string $id)
    {
        $request->user()->following()->detach($id);
        return response()->json(['message' => 'You have unfollowed this user.'], 200);
    }

    public function like(Request $request, string $id)
    {
        if(!$request->user()->likes()->where('receiver_id', $id)->exists()) {//if user has not liked this user already
            if($request->user()->liked_by()->where('sender_id', $id)->exists()) {//if user has been liked by this user already
                //update the pivot table to show that there is a match
                $request->user()->liked_by()->updateExistingPivot($id, ['is_matched' => true]);
                return response()->json(['message' => 'You have a match.', 'is_matched' => true], 200);
            }else {
                $request->user()->likes()->attach($id);
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response('', 204);
    }
}
