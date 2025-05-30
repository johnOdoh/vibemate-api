<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!request()->user()->verification) {
            return response()->json(['message' => 'You have not submitted any verification details'], 403);
        }
        $verification = request()->user()->verification;
        return response()->json([
            'id' => $verification->id,
            'type' => $verification->type,
            'created_at' => $verification->created_at->format('m d, Y')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'country' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'url' => 'nullable|url',
            'reason' => 'required|string|max:255',
        ]);

        $data['front'] = $request->file('front')->storeAs('verifications', $request->user()->profile->username.'-front', 'public');
        if ($request->hasFile('back')) {
            $data['back'] = $request->file('back')->storeAs('verifications', $request->user()->profile->username.'-back', 'public');
        }
        $request->user()->verification()->create($data);
        return response()->json(['message' => 'Verification request submitted successfully.'], 201);
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
