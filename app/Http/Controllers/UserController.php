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

    public function follow(string $id)
    {
        request()->user()->following()->attach($id);
        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(string $id)
    {
        request()->user()->following()->detach($id);
        return response()->json(['message' => 'You have unfollowed this user.'], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response('', 204);
    }
}
