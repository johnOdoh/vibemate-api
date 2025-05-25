<?php

namespace App\Http\Controllers;

use App\Models\EntryComment;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;

class EntryCommentController extends Controller
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
            'entry_id' => 'required|integer',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $comment = new EntryComment($data);
        if(!screenInput($data['body'])) $comment->status = 'pending';
        else {
            $comment->entry->no_of_engagements++;
            if($comment->entry->user_id !== $comment->user_id) {
                Notification::create([
                    'user_id' => $comment->entry->user_id,
                    'action' => 'entry comment',
                    'action_id' => $comment->entry->id,
                    'message' => "{$request->user()->profile->username} commented on your entry",
                ]);
            }
        }
        $comment->push();
        return response()->json(['message' => 'Comment created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return CommentResource::collection(EntryComment::where('entry_id', $id)->where('status', 'approved')->orderByDesc('created_at')->get());
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
    public function update(Request $request, EntryComment $entry_comment)
    {
        if(request()->user()->id !== $entry_comment->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $entry_comment->entry->no_of_engagements--;
            $entry_comment->push();
            $data['status'] = 'pending';
        }
        $entry_comment->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntryComment $entry_comment)
    {
        if(request()->user()->id !== $entry_comment->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $entry_comment->entry->no_of_engagements -= $entry_comment->replies->count() + 1;
        $entry_comment->entry->save();
        $entry_comment->delete();
        return response('', 204);
    }
}
