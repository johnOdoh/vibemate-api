<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use App\Mail\ResetToken;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) //on success, user should be directed to page to enter email verification token
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string',
            'isSocial' => 'required|boolean'
        ]);

        // $data = $validator->getData();
        $token = null;
        $verified = false;
        if (!$data['isSocial']) {
            $token = random_int(100000, 999999);
            Mail::to($data['email'])->send(new VerifyEmail($token));
        }else {
            $verified = true;
        }
        $user = new User([
            'username' => $data['username'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_verified' => $verified,
            'email_token' => $token
        ]);
        $user->save();
        return response($user->id, 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'email' => 'required|string',
            'isSocial' => 'required|boolean'
        ]);
        // if($validator->fails()) abort(422);

        $user = User::where('email', $request->email)->first();
        if ($request->isSocial) {//if user used a social signin
            if($user) {//if user exiats
                if($user->is_verified) { //if user has verified account
                    $user->token = $user->createToken("android device")->plainTextToken;
                    return new LoginResource($user);
                }
                else abort(403); //if user has not verified their email
            }
            abort(404);//if user does not exist
        }else{//if user did not use a social signin
            if($user) {
                if(Hash::check($request->password, $user->password)) {//if user entered correct password
                    if($user->is_verified) {
                        $user->token = $user->createToken("android device")->plainTextToken;
                        return new LoginResource($user);
                    }  //if user has verified account
                    else abort(403);
                }
            }
            abort(401);//if user does not exist or entered wrong password
        }
    }

    public function verifyEmail(Request $request) //on success, user should be directed to login page
    {
        $user = User::firstWhere('email', $request->email);
        if($user && ($user->email_token == $request->token)) {
            $user->is_verified = true;
            $user->email_token = null;
            $user->save();
            return response('', 200);
        }else abort(403);
    }

    public function resendEmailToken(Request $request) //on success, user should be directed to page to enter email reset token
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        // if($validator->fails()) abort(422);

        $token = random_int(100000, 999999);
        $user = User::firstWhere('email', $request->email);
        if(!$user) abort(404);
        Mail::to($request->email)->send(new VerifyEmail($token));
        $user->email_token = $token;
        $user->save();
        return;
    }

    public function sendPasswordResetToken(Request $request) //on success, user should be directed to page to enter token and new password
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $token = random_int(100000, 999999);
        $user = User::firstWhere('email', $request->email);
        if(!$user) abort(404);
        Mail::to($request->email)->send(new ResetToken($token));
        $user->reset_token = $token;
        $user->save();
        return;
    }

    public function resetPassword(Request $request) //on success, user should be directed to login page
    {
        $user = User::firstWhere('email', $request->email);
        if($user && ($user->reset_token == $request->token)) {
            $user->password = Hash::make($request->password);
            $user->reset_token = null;
            $user->save();
            return response('', 200);
        }else abort(401);
    }
}
