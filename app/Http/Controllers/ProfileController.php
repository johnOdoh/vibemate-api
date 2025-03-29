<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Validator;

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

    public function checkUsername(Request $request)
    {
        $suggestions = [];
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/', function ($attribute, $value, $fail) use (&$suggestions) {
                if (Profile::where('username', $value)->exists()) {
                    // Generate unique username suggestions
                    $suggestions = $this->generateUsernameSuggestions($value);
                    // Return validation failure with suggestions
                    $fail('The username is already taken.');
                }
            }]
        ], [
            'username.regex' => 'Username can only contain letters, numbers and underscores. No spaces are allowed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Username is already taken',
                'errors' => $validator->errors(),
                'suggestions' => $suggestions
            ], 422);
        }

        return response()->json(['message' => 'Username is available']);
    }

    /**
     * Generate unique username suggestions based on availability.
     */
    private function generateUsernameSuggestions($username)
    {
        $suggestions = [];
        $baseUsernames = [
            $username . rand(100, 999),
            $username . '_' . rand(10, 99),
            rand(10, 99) . $username,
            $username . '_official',
            $username . '_real',
            $username . '_dev'
        ];

        foreach ($baseUsernames as $suggestedUsername) {
            if (!Profile::where('username', $suggestedUsername)->exists()) {
                $suggestions[] = $suggestedUsername;
            }

            // Stop once we have 3 unique suggestions
            if (count($suggestions) >= 3) {
                break;
            }
        }

        return $suggestions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->user()->profile()->exists()) return response()->json(['message' => 'Profile already exists'], 403);
        $data = $request->validate([
            'username' => 'required|string|regex:/^[a-zA-Z0-9_]+$/|min:3|max:20|unique:profiles,username',
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
            'username.regex' => 'Username can only contain letters, numbers and underscores. No spaces are allowed',
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
    public function show(Profile $profile)
    {
        return $profile->user;
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
