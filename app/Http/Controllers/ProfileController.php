<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Mail\NearbyJoined;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new ProfileResource(request()->user()->profile, true);
    }

    /**
     * searching for records.
     */
    public function search(Request $request)
    {
        $input = $request->input('input');
        return Profile::whereLike('username', "%$input%")
            ->orWhereLike('firstname', "%$input%")
            ->orWhereLike('lastname', "%$input%")
            ->limit(10)
            ->get()
            ->map(fn ($profile) => [
                'id' => $profile->user->id,
                'username' => $profile->username,
                'firstname' => $profile->firstname,
                'lastname' => $profile->lastname,
                'image' => asset('storage/' . $profile->image)
            ]);
    }

    public function getVisitors(Request $request)
    {
        return $request->user()->profile->visitors
            ->map(fn ($profile) => [
                'id' => $profile->user->id,
                'username' => $profile->username,
                'firstname' => $profile->firstname,
                'lastname' => $profile->lastname,
                'image' => asset('storage/' . $profile->image),
                'visited_count' => $profile->pivot->count
            ]);
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
        if($request->user()->profile()->exists()) {
            return new ProfileResource($request->user()->profile);
        }
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
            'interests.*' => 'integer',
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $moderation_result = moderateImage($request->file($attribute)->getRealPath(), true);
                    if(!$moderation_result['status']) {
                        $fail("Your profile image failed our content moderation policy due to {$moderation_result['reason']}");
                    }
                }
            ],
            'city' => 'required|string',
            'country' => 'required|string'
        ], [
            'username.regex' => 'Username can only contain letters, numbers and underscores. No spaces are allowed',
            'firstname.regex' => 'First name must contain only letters, hyphens, and apostrophes. No spaces allowed.',
            'lastname.regex' => 'Last name must contain only letters, hyphens, and apostrophes. No spaces allowed.'
        ]);

        $imageName = $request->username.'.'.$request->file('image')->extension();
        $path = "profile_images/$imageName";
        if (!Storage::disk('s3')->put($path, file_get_contents($request->file('image')), 'public')) {
            return response()->json(['message' => 'Failed to upload profile image'], 500);
        }
        $data['image'] = $path;
        $data['user_id'] = $request->user()->id;
        // $data['meta_location'] = getCountryFromIp();
        $data['here_for'] = implode(', ', $data['here_for']);
        unset($data['interests']);
        $profile = new Profile($data);
        $user = $request->user()->referredBy()->first();
        $location = [
            'longitude' => $request->longitude ?? 0,
            'latitude' => $request->latitude ?? 0,
            'city' => $request->state ?? $profile->city,
            'country' => $request->country ?? $profile->country,
        ];
        User::where('users.is_subscribed', true)
            ->getDistance($location['latitude'], $location['longitude'])
            ->having('distance', '<', 20)
            ->chunk(100, function ($users) {
                $users->each(function ($user) {
                    Mail::to($user)->send(new NearbyJoined($user->profile->firstname));
                });
            });
        DB::beginTransaction();
        try {
            if($user) {
                if(($user->referral_count + 1) % 7 == 0) {
                    $user->referral_amount_earned += 14.00;
                }
                $user->referral_count += 1;
                $user->save();
            }
            $profile->save();
            $profile->user->reward()->create();
            $profile->user->discovery()->create();
            $request->user()->interests()->attach($request->interests);

            DB::commit(); // If everything succeeds, commit the transaction
            User::where('users.is_subscribed', true)
                ->getDistance($location['latitude'], $location['longitude'])
                ->having('distance', '<', 20)
                ->chunk(100, function ($users) {
                    $users->each(function ($user) {
                        Mail::to($user)->send(new NearbyJoined($user->profile->firstname));
                    });
                });
            return response()->json([
                'message' => 'Profile created successfully',
                'profile' => new ProfileResource($profile)
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // If anything fails, rollback all changes
            return $e;
            // return response()->json(['message' => 'An unexpected error occurred. Please try again'], 400); // Optionally, handle the error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $profile = $user->profile;
        if (!$user->discovery()->exists()) {
            $user->discovery()->create();
        }
        if ($request->user()->profile->visited()->where('visited_id', $profile->id)->exists()) {
            $request->user()->profile->visited()
                ->updateExistingPivot($profile->id, ['count' => DB::raw('count + 1')]);
        } else {
            $request->user()->profile->visited()->attach($profile->id);
        }
        return response()->json([
            'user' => new UserResource($user),
            'profile' => new ProfileResource($profile, true)
        ]);
    }

    public function markAsVisited(Request $request, $id)
    {
        if (!Profile::where('id', $id)->exists())
            return response()->json(['message' => 'Profile not found'], 404);
        if ($request->user()->profile->visited()->where('visited_id', $id)->exists()) {
            $request->user()->profile->visited()->updateExistingPivot($id, ['count' => DB::raw('count + 1')]);
        } else {
            $request->user()->profile->visited()->attach($id);
        }
        return response()->json(['message' => 'Profile marked as visited']);
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
        $data = $request->validate([
            'bio' => 'nullable|string',
            'phone' => 'required|string',
            'interests' => 'required|array',
            'interests.*' => 'integer',
            'city' => 'required|string',
            'country' => 'required|string'
        ]);
        $request->user()->profile()->update([
            'bio' => $data['bio'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'country' => $data['country']
        ]);
        $request->user()->interests()->sync($data['interests']);
        return response()->json(['message' => 'Profile updated successfully']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function changeImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $moderation_result = moderateImage($request->file($attribute)->getRealPath(), true);
                    if(!$moderation_result['status']) {
                        $fail("Your profile image failed our content moderation policy due to {$moderation_result['reason']}");
                    }
                }
            ],
        ]);
        $user = $request->user();
        if (Storage::disk('s3')->put($user->profile->image, file_get_contents($request->file('image')), 'public')) {
            return response()->json(['message' => 'Profile image updated successfully']);
        }
        else
            return response()->json(['message' => 'Failed to update profile image'], 500);
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'setting' => 'required|string|in:email_notifications,receive_messages,online_status,top_picks,enable_discovery,global',
            'value' => 'required|boolean'
        ]);
        $user = $request->user();
        $user->profile->{$request->setting} = $request->value;
        $user->profile->save();
        return response()->json(['message' => 'Setting updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
