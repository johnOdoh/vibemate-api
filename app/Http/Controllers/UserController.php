<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function user(User $user)
    {
        return response($user, 200);
    }

    public function signout(User $user)
    {
        $user->tokens()->delete();
        return response('', 200);
    }
}
