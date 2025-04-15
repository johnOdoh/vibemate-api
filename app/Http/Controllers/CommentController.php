<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
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
        if(!$comment->post->allow_comments) return response()->json(['message' => 'Comments are not allowed on this post'], 403);
        if(!screenInput($data['body'])) $comment->status = 'pending';
        else $comment->post->no_of_engagements++;
        $comment->push();
        return response()->json(['message' => 'Comment created successfully'], 201);
    }

    /**
     * Display the specified resource.
     * $id is the post id
     */
    public function show(string $id)
    {
        $post = Post::findOrFail($id);
        return CommentResource::collection($post->comments()->where('status', 'approved')->orderByDesc('id')->get());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $comment->post->no_of_engagements--;
            $comment->push();
            $data['status'] = 'pending';
        }
        $comment->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $comment->post->no_of_engagements -= $comment->replies->count() + 1;
        $comment->post->save();
        $comment->delete();
        return response('', 204);
    }
}
