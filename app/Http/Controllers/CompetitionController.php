<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompetitionResource;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $competitions = Competition::where('status', 'approved')->get();
        return CompetitionResource::collection($competitions);
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
            'name' => 'required|string|unique:competitions',
            'description' => 'required|string',
            'hashtag' => 'required|string|regex:/^#([a-z0-9]+)/|unique:competitions',
            'type' => 'required|string',
            'category' => 'required|string',
            'prize' => 'required|numeric',
            'start' => 'required|date',
            'end' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ], [
            'hashtag.regex' => 'hashtag must start with a # and contain only lowercase letters and numbers.'
        ]);
        if($request->user()->wallet < ($data['prize'] + (0.1 * $data['prize']))) {
            return response()->json(['message' => 'Insufficient funds.'], 403);
        }
        $request->user()->wallet -= ($data['prize'] + (0.1 * $data['prize']));
        $imageName = time().'-'.$request->user()->id.'.'.$request->file('image')->getClientOriginalExtension();
        $path = $request->file('image')->storeAs('competitions', $imageName, 'public');
        $data['image'] = $path;
        $data['user_id'] = $request->user()->id;
        $competition = new Competition($data);
        $request->user()->save();
        $competition->save();
        return response()->json(['message' => 'Competition created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition)
    {
        return new CompetitionResource($competition);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition)
    {
        if($competition->status !== 'pending') return response()->json(['message' => 'Competition can no longer be updated.'], 403);
        $data = $request->validate([
            'name' => ['required', 'string',
                Rule::unique('competitions', 'name')->ignore($competition->name),
                Rule::in([$competition->name]),
            ],
            'hashtag' => ['required', 'string', 'regex:/^#([a-z0-9]+)$/',
                Rule::unique('competitions', 'hashtag')->ignore($competition->hashtag),
                Rule::in([$competition->hashtag]),
            ],
            'description' => 'required|string',
            'type' => 'required|string',
            'category' => 'required|string',
            'prize' => 'required|numeric',
            'start' => 'required|date',
            'end' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ], [
            'hashtag.regex' => 'hashtag must start with a # and contain only lowercase letters and numbers.'
        ]);
        if($request->hasFile('image'))  $request->file('image')->storeAs('competitions', $competition->image, 'public');
        unset($data['image']);
        $competition->update($data);
        return response()->json(['message' => 'Competition updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition)
    {
        if($competition->status !== 'pending') abort(403);
        unlink(public_path('storage/'.$competition->image));
        $competition->delete();
        return response()->noContent();
    }
}
