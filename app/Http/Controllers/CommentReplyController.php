<?php

namespace App\Http\Controllers;

use App\Models\CommentReply;
use Illuminate\Http\Request;

class CommentReplyController extends Controller
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
            'comment_id' => 'required|integer',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $reply = new CommentReply($data);
        if(!screenInput($data['body'])) $reply->status = 'pending';
        else $reply->comment->post->no_of_engagements++;
        $reply->push();
        return response()->json(['message' => 'Comment created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CommentReply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommentReply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommentReply $reply)
    {
        if($request->user()->id !== $reply->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $reply->comment->post->no_of_engagements--;
            $reply->push();
            $data['status'] = 'pending';
        }
        $reply->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommentReply $reply)
    {
        if(request()->user()->id !== $reply->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $reply->comment->post->no_of_engagements--;
        $reply->comment->post->save();
        $reply->delete();
        return response('', 204);
    }
}
