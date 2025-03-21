<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Cloudinary\Cloudinary;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Http\Resources\EntryResource;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $cloudinary = new Cloudinary();
        // $cloudinary->uploadApi()->upload;
        if(str_starts_with($file->getMimeType(), 'image/')) {
            $path = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'entries/images',
                'public_id' => time(true).'-'.$request->user()->id
            ])['secure_url'];
        }
        else {
            $path = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'resource_type' => 'video',
                'folder' => 'entries/videos',
                'public_id' => time(true).'-'.$request->user()->id
            ]);
            $data['is_image'] = false;
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
        return $request;
    }

    /**
     * Like the specified entry.
     */
    public function like(Entry $entry)
    {
        $entry->likes()->create(['user_id' => request()->user()->id]);
        $entry->no_of_engagements++;
        $entry->save();
        return response()->json(['message' => 'Entry liked'], 201);;
    }

    /**
     * Like the specified entry.
     */
    public function unlike(Entry $entry)
    {
        $entry->likes()->where('user_id', request()->user()->id)->delete();
        if($entry->no_of_engagements > 0) $entry->no_of_engagements--;
        $entry->save();
        return response()->json(['message' => 'Entry unliked'], 201);;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entry $entry)
    {
        if(request()->user()->id !== $entry->user_id)
            return response()->json(['message' => 'You are not authorized to delete this entry'], 403);
        if($entry->url){
            unlink(public_path('storage/'.$entry->url));
        }
        $entry->delete();
        return response()->noContent();
    }
}
