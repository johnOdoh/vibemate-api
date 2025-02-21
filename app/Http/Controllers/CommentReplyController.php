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
        $reply->comment->post->no_of_engagements++;
        $reply->push();
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
    public function edit(CommentReply $reply)
    {
        return response($reply, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommentReply $reply)
    {
        if($request->user()->id !== $reply->user_id) abort(403);
        $request->validate([
            'body' => 'required|string'
        ]);
        $reply->update($request->all());
        return response($reply, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommentReply $reply)
    {
        if(request()->user()->id !== $reply->user_id) abort(403);
        $reply->comment->post->no_of_engagements--;
        $reply->comment->post->save();
        $reply->delete();
        return response('', 204);
    }
}
