<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $guarded = [];

    public function gifts()
    {
        return $this->hasMany(Gift::class);
    }
}
