<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new ProfileResource(request()->user());
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
        if($request->user()->profile()->exists()) return response()->json(['message' => 'Profile already exists'], 400);
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
        $data['image'] = $path;
        $data['user_id'] = $request->user()->id;
        $data['meta_location'] = getCountryFromIp();
        $data['here_for'] = implode(', ', $data['here_for']);
        unset($data['interests']);
        $profile = new Profile($data);

        DB::beginTransaction();
        try {
            $profile->save();
            $request->user()->interests()->attach($request->interests);

            DB::commit(); // If everything succeeds, commit the transaction
            return response()->json(['message' => 'Profile created'], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // If anything fails, rollback all changes
            return response()->json(['message' => 'An unexpected error occurred. Please try again'], 400); // Optionally, handle the error
        }
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
