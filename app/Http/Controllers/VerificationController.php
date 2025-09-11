<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'status' => $verification->status,
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
            'reason' => 'required|string|max:255'
        ]);
        if ($request->user()->verification()->exists()) {
            return response()->json(['message' => 'You have already submitted a verification request.'], 403);
        }
        $fileName = $request->user()->profile->username.'-front.'.$request->file('front')->extension();
        Storage::disk('s3')->put("verifications/$fileName", file_get_contents($request->file('front')), 'public');
        $data['front'] = "verifications/$fileName";
        if ($request->hasFile('back')) {
            $fileName = $request->user()->profile->username.'-back.'.$request->file('back')->extension();
            Storage::disk('s3')->put("verifications/$fileName", file_get_contents($request->file('back')), 'public');
            $data['back'] = "verifications/$fileName";
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
