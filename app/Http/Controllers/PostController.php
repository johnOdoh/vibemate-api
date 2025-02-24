<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PostResource::collection(Post::all());
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
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = implode(', ', $data['hashtags']);
        $data['user_id'] = $request->user()->id;
        $post = new Post($data);
        $post->save();
        return response($post, 201);
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
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = implode(', ', $data['hashtags']);
        $post->update($data);
        return response($post, 201);
    }

    /**
     * Like the specified post.
     */
    public function like(Post $post)
    {
        $post->likes()->create(['user_id' => request()->user()->id]);
        $post->no_of_engagements++;
        $post->save();
        return response($post, 200);
    }

    /**
     * Like the specified post.
     */
    public function unlike(Post $post)
    {
        $post->likes()->where('user_id', request()->user()->id)->delete();
        if($post->no_of_engagements > 0) $post->no_of_engagements--;
        $post->save();
        return response($post, 200);
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
        return response(null, 204);
    }
}
