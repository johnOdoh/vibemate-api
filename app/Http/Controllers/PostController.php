<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
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
            'user_id' => 'required|integer',
            'privacy' => 'required|string',
            'allow_comments' => 'required|boolean',
            'allow_like_counts' => 'required|boolean',
            'body' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'location' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        $file = $request->file('file');
        $path = null;
        if($file) {
            if(str_starts_with($file->getMimeType(), 'image/')) {
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('posts/images', $fileName, 'public');
            }
            else {
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('posts/videos', $fileName, 'public');
            }
        }
        unset($data['file']);
        $data['file_url'] = $path;
        $post = new Post($data);
        $post->save();
        return response($post, 201);
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
