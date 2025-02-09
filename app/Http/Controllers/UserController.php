<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
