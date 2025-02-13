<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryCommentReply extends Model
{
    protected $fillable = ['entry_comment_id', 'user_id', 'body'];

    public function entryComment()
    {
        return $this->belongsTo(EntryComment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
