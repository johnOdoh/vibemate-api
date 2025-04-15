<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusResource;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['statuses' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->whereHas('statuses') // Only users who have posted a status
        ->withMax('statuses', 'created_at') // Add virtual column: latest status timestamp
        ->orderByDesc('statuses_max_created_at') // Sort users by latest status
        ->get();
        return response()->json(
            [
                'my_status' => request()->user()->statuses->map(fn ($status) => [
                    "id" => $status->id,
                    "content" => $status->type !== 'text' ? asset('storage/'.$status->content) : $status->content,
                    "type" => $status->type,
                    "created_at" => $status->created_at
                ]),
                'users' => StatusResource::collection($users)
            ]
        );
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
        $request->validate([
            'content' => 'required|array',
            'content.file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $file = $request->file($attribute);
                    if(str_starts_with($file->getMimeType(), 'image/')) {
                        $moderation_result = moderateImage($file->getRealPath(), true);
                        if(!$moderation_result['status']) {
                            $fail("The value in $attribute failed our content moderation test due to {$moderation_result['reason']}.");
                        }
                    }
                }
            ],
            'content.text' => ['nullable', 'string', 'max:191',
                function ($attribute, $value, $fail) use ($request) {
                    if (!screenInput($value)) {
                        $fail("The value in $attribute failed our content moderation test.");
                    }
                }
            ],
        ]);
        // return $request->content;
        foreach($request->content as $key => $value) {
            if ($request->hasFile("content.$key.file")) {
                $file = $request->file("content.$key.file");
                if(str_starts_with($file->getMimeType(), 'image/')) {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $content = $file->storeAs('statuses/images', $fileName, 'public');
                    $type = 'image';
                }
                else {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $content = $file->storeAs('statuses/videos', $fileName, 'public');
                    $type = 'video';
                }
            } else $type = 'text';
            $text = $value['text'] ?? null;
            Status::create([
                'user_id' => request()->user()->id,
                'file' => $content ?? null,
                'text' => $text ?? null,
                'type' => $type
            ]);
            $content = $text = $type = null;
        }
        return response()->json(['message' => 'Story added successfully'], 201);
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
