<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Competition;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\EntryResource;
use Illuminate\Support\Facades\Storage;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entries = Entry::where('competition_id', request()->competition_id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
        return EntryResource::collection($entries);
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
            'competition_id' => 'required|numeric',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        $competition = Competition::findOrFail($data['competition_id']);
        if($competition->status !== 'approved' || $competition->phase === 'upcoming') {
            return response()->json(['message' => 'Entry submission for this competition is not available yet'], 403);
        }
        if($competition->phase === 'ended') {
            return response()->json(['message' => 'Entry submission for this competition has ended'], 403);
        }
        if($competition->user_id == $request->user()->id) {
            return response()->json(['message' => 'You cannot submit an entry for your own competition'], 403);
        }
        if($competition->entries()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'You have already submitted an entry for this competition'], 403);
        }
        $file = $request->file('file');
        // $cloudinary->uploadApi()->upload;
        if(str_starts_with($file->getMimeType(), 'image/')) {
            $moderationResult = moderateImage($file->getRealPath(), true);
            if(!$moderationResult['status']) {
                $data['status'] = 'pending';
                $data['remark'] = $moderationResult['reason'];
            }
            $path = 'entries/images/'.$file->hashname();
            Storage::disk('s3')->put($path, file_get_contents($file), 'public');
        }
        else {
            $path = 'entries/videos/'.$file->hashname();
            Storage::disk('s3')->put($path, file_get_contents($file), 'public');
            // $data['media_id'] = moderateVideo($path);
        }
        unset($data['file']);
        $data['url'] = $path;
        $data['user_id'] = $request->user()->id;
        $post = new Entry($data);
        $post->save();
        return response()->json(['message' => 'Entry submitted successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Entry $entry)
    {
        return new EntryResource($entry);
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
     * Like the specified entry.
     */
    public function like(Request $request, Entry $entry)
    {
        $entry->likes()->create(['user_id' => request()->user()->id]);
        $entry->no_of_engagements++;
        $entry->save();
        if($entry->user_id !== request()->user()->id) {
            Notification::create([
                'user_id' => $entry->user_id,
                'action' => 'entry like',
                'action_id' => $entry->id,
                'message' => "{$request->user()->profile->username} liked your entry",
            ]);
        }
        return response()->json(['message' => 'Entry liked'], 201);;
    }

    /**
     * Like the specified entry.
     */
    public function unlike(Entry $entry)
    {
        $status = $entry->likes()->where('user_id', request()->user()->id)->delete();
        if($status && $entry->no_of_engagements > 0) $entry->no_of_engagements--;
        $entry->save();
        return response()->json(['message' => 'Entry unliked'], 200);;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entry $entry)
    {
        if(request()->user()->id !== $entry->user_id)
            return response()->json(['message' => 'You are not authorized to delete this entry'], 403);
        if($entry->url){
            Storage::disk('s3')->delete($entry->url);
        }
        $entry->delete();
        return response()->noContent();
    }
}
