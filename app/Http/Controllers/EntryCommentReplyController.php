<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\EntryCommentReply;

class EntryCommentReplyController extends Controller
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
            'entry_comment_id' => 'required|integer',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $reply = new EntryCommentReply($data);
        if(!screenInput($data['body'])) $reply->status = 'pending';
        else {
            $reply->entryComment->entry->no_of_engagements++;
            if($reply->entryComment->user_id !== $reply->user_id) {
                Notification::create([
                    'user_id' => $reply->entryComment->user_id,
                    'action' => 'entry comment reply',
                    'action_id' => $reply->entryComment->entry->id,
                    'message' => "{$request->user()->profile->username} replied to your comment",
                ]);
            }
            if($reply->entryComment->entry->user_id !== $reply->user_id) {
                Notification::create([
                    'user_id' => $reply->entryComment->entry->user_id,
                    'action' => 'entry comment',
                    'action_id' => $reply->entryComment->entry->id,
                    'message' => "{$request->user()->profile->username} commented on your entry",
                ]);
            }
        }
        $reply->push();
        return response()->json(['message' => 'Comment created successfully'], 201);
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
    public function update(Request $request, EntryCommentReply $entry_comment_reply)
    {
        if($request->user()->id !== $entry_comment_reply->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $entry_comment_reply->entryComment->entry->no_of_engagements--;
            $entry_comment_reply->push();
            $data['status'] = 'pending';
        }
        $entry_comment_reply->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntryCommentReply $entry_comment_reply)
    {
        if(request()->user()->id !== $entry_comment_reply->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $entry_comment_reply->entryComment->entry->no_of_engagements--;
        $entry_comment_reply->entryComment->entry->save();
        $entry_comment_reply->delete();
        return response('', 204);
    }
}
