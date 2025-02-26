<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;

class UserController extends Controller
{
    public function index()
    {
        return new ProfileResource(request()->user());
    }

    public function signout(User $user)
    {
        $user->tokens()->delete();
        return response('', 200);
    }
}
