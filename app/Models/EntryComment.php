<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryComment extends Model
{
    protected $fillable = ['entry_id', 'user_id', 'body'];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(EntryCommentReply::class);
    }
}
