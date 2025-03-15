<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Services\PerspectiveService;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $current_location = getCountryFromIp();
        $following = request()->user()->following->pluck('id');
        $query = Post::query();
        $query->where(function ($q) use ($following) {
            $q->where('status', 'approved'); //post is approved
            $q->whereIn('user_id', $following); //post belongs to a user you are following
            $q->whereNot('privacy', 'private'); //post is not private
        });
        $query->orWhere(function ($q) use ($current_location) {
            $q->where('status', 'approved'); //post is approved
            $q->where('meta_location', $current_location); //post originated from your current location
            $q->where('privacy', 'public'); //post is set to public
            $q->where('user_id', '!=', request()->user()->id); //post does not belong to you
        });
        $posts = $query->get();
        return PostResource::collection($posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function test()
    {
        $text = request()->input('text');
        $perspectiveService = new PerspectiveService;
        return $perspectiveService->analyzeText($text);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'privacy' => 'required|string',
            'allow_comments' => 'required|boolean',
            'allow_like_counts' => 'required|boolean',
            'body' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'location' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        $path = [];
        if($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if(str_starts_with($file->getMimeType(), 'image/')) {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $path[] = $file->storeAs('posts/images', $fileName, 'public');
                }
                else {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $path[] = $file->storeAs('posts/videos', $fileName, 'public');
                }
            }
        }
        unset($data['files']);
        if($data['body'] && !screenInput($data['body'])) $data['status'] = 'pending';
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = implode(', ', $data['hashtags']);
        $data['user_id'] = $request->user()->id;
        $data['meta_location'] = getCountryFromIp();
        $post = new Post($data);
        $post->save();
        return response()->json(['message' => 'Post created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post);
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
    public function update(Request $request, Post $post)
    {
        if($request->user()->id !== $post->user_id) abort(403);
        // return $request->all();
        $data = $request->validate([
            'privacy' => 'required|string',
            'allow_comments' => 'required|boolean',
            'allow_like_counts' => 'required|boolean',
            'body' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'location' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        if($post->file_url){
            $files = explode(', ', $post->file_url);
            foreach ($files as $file) {
                unlink(public_path('storage/'.$file));
            }
        }
        $path = [];
        if($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if(str_starts_with($file->getMimeType(), 'image/')) {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $path[] = $file->storeAs('posts/images', $fileName, 'public');
                }
                else {
                    $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                    $path[] = $file->storeAs('posts/videos', $fileName, 'public');
                }
            }
        }
        unset($data['files']);
        if($data['body'] && !screenInput($data['body'])) $data['status'] = 'pending';
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = implode(', ', $data['hashtags']);
        $post->update($data);
        return response()->json(['message' => 'Post updated successfully']);;
    }

    /**
     * Like the specified post.
     */
    public function like(Post $post)
    {
        $post->likes()->create(['user_id' => request()->user()->id]);
        $post->no_of_engagements++;
        $post->save();
        return response()->json(['message' => 'Post liked successfully'], 201);;
    }

    /**
     * Like the specified post.
     */
    public function unlike(Post $post)
    {
        $post->likes()->where('user_id', request()->user()->id)->delete();
        if($post->no_of_engagements > 0) $post->no_of_engagements--;
        $post->save();
        return response()->json(['message' => 'Post unliked successfully'], 201);;
    }

    public function getTopHashtags()
    {
        return Post::getTopHashtags(10, 7);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if($post->file_url){
            $files = explode(', ', $post->file_url);
            foreach ($files as $file) {
                unlink(public_path('storage/'.$file));
            }
        }
        $post->delete();
        return response()->noContent();
    }

    public function searchArtist($query)
    {
        $token = getSpotifyToken();

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('https://api.spotify.com/v1/search', [
            'q' => $query,
            'type' => 'track',
            'limit' => 2,
            'include_external' => 'audio',
            'fields' => 'tracks.items(name,artists(name),uri'
        ]);

        return $response->json();
    }
}
