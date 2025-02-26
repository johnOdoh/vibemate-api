<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profile = request()->user()->profile;
        $interests = $profile->user->interests->pluck('name');
        $interested_in = $profile->connect_with;
        $gender = $profile->gender;
        $here_for = explode(', ', $profile->here_for);

        $query = User::query();
        $query->whereHas('interests', function ($q) use ($interests) {
            $q->whereIn('name', $interests);
        });
        $query->whereHas('profile', function ($q) use ($interested_in, $here_for, $gender) {
            $q->where(function ($q) use ($here_for) {
                foreach ($here_for as $item) {
                    $q->orWhere('here_for', 'like', "%$item%");
                }
            });
            $q->where(function ($q) use ($gender) {
                $q->where('connect_with', $gender)->orWhere('connect_with', 'everyone');
            });
            if($interested_in !== 'everyone') {
                $q->where('gender', $interested_in);
            }
        });
        $users = $query->get();
        return ProfileResource::collection($users);
    }

    /**
     * searching for records.
     */
    public function search()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'-]+$/|max:255',
            'lastname' => 'required|string|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'-]+$/|max:255',
            'dob' => 'required|date',
            'connect_with' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'here_for' => 'required|array',
            'interests' => 'required|array',
            'image' => 'required|image',
            'city' => 'required|string',
            'country' => 'required|string'
        ], [
            'firstname.regex' => 'First name must contain only letters, hyphens, and apostrophes. No spaces allowed.',
            'lastname.regex' => 'Last name must contain only letters, hyphens, and apostrophes. No spaces allowed.'
        ]);
        $imageName = $request->user()->username.'.'.$request->file('image')->getClientOriginalExtension();
        $path = $request->file('image')->storeAs('profile_images', $imageName, 'public');
        $imageUrl = asset("storage/$path");
        $data['image'] = $imageUrl;
        $data['user_id'] = $request->user()->id;
        // $data['location'] = getCountryFromIp();
        $data['here_for'] = implode(', ', $data['here_for']);
        unset($data['interests']);
        $profile = new Profile($data);
        $profile->save();
        $request->user()->interests()->attach($request->interests);
        return response()->json($profile, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
