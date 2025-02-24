<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
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
            'post_id' => 'required|integer',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $comment = new Comment($data);
        if(!$comment->post->allow_comments) abort(403, 'Comments are not allowed on this post');
        $comment->post->no_of_engagements++;
        $comment->push();
        return response('', 201);
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
    public function edit(Comment $comment)
    {
        return response($comment, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id) abort(403);
        $request->validate([
            'body' => 'required|string'
        ]);
        $comment->update($request->all());
        return response($comment, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id) abort(403);
        $comment->post->no_of_engagements -= $comment->replies->count() + 1;
        $comment->post->save();
        $comment->delete();
        return response('', 204);
    }
}
