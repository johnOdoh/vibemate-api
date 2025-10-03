<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\Request;
use App\Mail\CompetitionWinner;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CompetitionResource;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $is_live = $request->query('live', 'false');
        if($is_live === 'true') {
            $competitions = Competition::where('status', 'approved')
                ->where('phase', 'active')
                ->orderByDesc('created_at')
                ->get();
            return CompetitionResource::collection($competitions);
        }
        $competitions = Competition::where('status', 'approved')
            ->orderByDesc('created_at')
            ->get();
        return response()->json([
            'available_tokens' => $request->user()->wallet,
            'amount_in_pounds' => $request->user()->wallet * 0.01,
            'competitions' => CompetitionResource::collection($competitions)
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
            'name' => 'required|string|unique:competitions',
            'description' => 'required|string',
            'hashtag' => 'required|string|regex:/^#([a-z0-9]+)/|unique:competitions',
            'type' => 'required|string',
            'category' => 'required|string',
            'is_multiple' => 'required|boolean',
            'prize' => ['required', 'numeric', 'min:1000',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_multiple) {
                        if ($request->prize_1 + $request->prize_2 + $request->prize_3 != $value) {
                            $fail('The sum of the prizes must be equal to the total prize.');
                        }
                    }
                },
            ],
            'prize_1' => 'required_if:is_multiple,true|numeric|min:1',
            'prize_2' => ['required_if:is_multiple,true', 'numeric', 'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->prize_1) {
                        $fail('2nd prize cannot be greater than 1st prize.');
                    }
                },
            ],
            'prize_3' => ['required_if:is_multiple,true', 'numeric', 'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->prize_1 || $value > $request->prize_2) {
                        $fail('3rd prize cannot be greater than 1st or 2nd prize.');
                    }
                },
            ],
            'start' => 'required|date',
            'end' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ], [
            'hashtag.regex' => 'hashtag must start with a # and contain only lowercase letters and numbers.',
            'prize.min' => 'The reward must be at least 1,000 VBM tokens.',
        ]);
        // if($request->user()->wallet < ($data['prize'] + (0.1 * $data['prize']))) {
        //     return response()->json(['message' => 'Insufficient funds.'], 403);
        // }
        $user = $request->user();
        $request->user()->wallet -= $data['prize'] + (0.1 * $data['prize']);
        $path = 'competitions/'.$request->file('image')->hashName();
        Storage::disk('s3')->put($path, file_get_contents($request->file('image')), 'public');
        if(!screenInput($data['name']) || !screenInput($data['description'])) {
            $data['status'] = 'pending';
        }
        $data['image'] = $path;
        $data['user_id'] = $user->id;
        if ($user->badge_status === 'active' || $user->is_subscribed || $user->followers()->where('is_active', true)->count() >= 1000 || $data['prize'] >= 50000) {
            $data['is_featured'] = true;
        }
        $competition = new Competition($data);
        $user->save();
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
        if($competition->phase !== 'upcoming') return response()->json(['message' => 'Competition can no longer be updated.'], 403);
        $data = $request->validate([
            'name' => ['required', 'string',
                Rule::unique('competitions', 'name')->ignore($competition->name)
            ],
            'hashtag' => ['required', 'string', 'regex:/^#([a-z0-9]+)$/',
                Rule::unique('competitions', 'hashtag')->ignore($competition->hashtag)
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
        if($request->hasFile('image'))
            Storage::disk('s3')->put($competition->image, file_get_contents($request->file('image')), 'public');
        unset($data['image']);
        $competition->update($data);
        return response()->json(['message' => 'Competition updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition)
    {
        if($competition->user_id !== request()->user()->id)
            return response()->json(['message' => 'You are not authorized to delete this competition.'], 403);
        if($competition->phase !== 'upcoming')
            return response()->json(['message' => 'You can no longer delete this competition.'], 403);
        Storage::disk('s3')->delete($competition->image);
        $competition->user->wallet += ($competition->prize + (0.1 * $competition->prize));
        $competition->user->save();
        $competition->delete();
        return response()->noContent();
    }

    public function start(Competition $competition)
    {
        if($competition->status !== 'approved') return response()->json(['message' => 'Competition has not been approved.'], 403);
        if($competition->phase === 'active') return response()->json(['message' => 'Competition has already started.'], 403);
        $competition->phase = 'active';
        $competition->save();
        return response()->json(['message' => 'Competition has started.'], 200);
    }

    public function end(Competition $competition)
    {
        if($competition->status !== 'approved') return response()->json(['message' => 'Competition has not been approved.'], 403);
        if($competition->phase === 'ended') return response()->json(['message' => 'Competition has already ended.'], 403);
        $competition->phase = 'ended';
        $competition->save();
        if($competition->is_multiple) {
            $competition->entries()->orderBy('no_of_engagements', 'desc')->take(3)->get()->each(function ($entry, $key) use ($competition) {
                if($key === 0) {
                    $entry->user->increment('wallet', $competition->prize_1);
                    Mail::to($entry->user)->send(new CompetitionWinner($entry->user->profile->firstname, $competition->name, $competition->prize_1, '1st'));
                }
                if($key === 1) {
                    $entry->user->increment('wallet', $competition->prize_2);
                    Mail::to($entry->user)->send(new CompetitionWinner($entry->user->profile->firstname, $competition->name, $competition->prize_2, '2nd'));
                }
                if($key === 2) {
                    $entry->user->increment('wallet', $competition->prize_3);
                    Mail::to($entry->user)->send(new CompetitionWinner($entry->user->profile->firstname, $competition->name, $competition->prize_3, '3rd'));
                }
            });
        }else {
            $entry = $competition->entries()->orderBy('no_of_engagements', 'desc')->first();
            $entry->user->increment('wallet', $competition->prize);
            Mail::to($entry->user)->send(new CompetitionWinner($entry->user->profile->firstname, $competition->name, $competition->prize, '1st'));
        }
        return response()->json(['message' => 'Competition has ended.'], 200);
    }
}
