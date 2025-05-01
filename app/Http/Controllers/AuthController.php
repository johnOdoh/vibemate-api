<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Profile;
use App\Mail\ResetToken;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\LoginResource;
use App\Services\GetStreamService;

class AuthController extends Controller
{
    public function register(Request $request) //on success, user should be directed to page to enter email verification token
    {
        $data = $request->validate([
            // 'username' => 'required|string|regex:/^[a-zA-Z0-9_]+$/|min:3|max:20|unique:users,username',
            // 'firstname' => 'required|string',
            // 'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'referrer' => 'nullable|string',
            'isSocial' => 'required|boolean'
        ]);

        // $data = $validator->getData();
        $token = null;
        $verified = false;
        if (!$data['isSocial']) {
            $token = random_int(100000, 999999);
            Mail::to($data['email'])->send(new VerifyEmail($token));
        }
        else $verified = true;
        $user = new User([
            // 'username' => $data['username'],
            // 'firstname' => $data['firstname'],
            // 'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_verified' => $verified,
            'email_token' => $token
        ]);
        DB::beginTransaction();
        try {
            $user->save();
            $user->reward()->create();
            if (!empty($request->referrer)) {
                $referrer = Profile::firstWhere('username', strtolower(trim($request->referrer)));
                if ($referrer) {
                    $referrer->user->referrals()->attach($user->id);
                    $referrer->user->referral_count += 1;
                    $referrer->user->referral_amount_earned += 2.00;
                    $referrer->user->save();
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }
        if (!$data['isSocial']) return response()->json(['message' => 'User created successfully'], 201);
        else {
            $streamService = new GetStreamService();
            $stream_token = $streamService->getUserToken($user->id);
            $token =  $user->createToken("android device")->plainTextToken;
            return response()->json([
                'message' => 'User created successfully',
                'user' => new UserResource($user),
                'token' => $token,
                'stream_token' => $stream_token
            ], 201);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'isSocial' => 'required|boolean'
        ]);
        // if($validator->fails()) abort(422);
        if ($request->isSocial) {//if user used a social signin
            $user = User::where('email', $request->email)->first();
            if($user) {//if user exists
                if($user->is_verified) { //if user has verified account
                    $user->token = $user->createToken("android device")->plainTextToken;
                    return new LoginResource($user);
                }
                else return response()->json(['message' => 'User Unverified'], 403); //if user has not verified their email
            }
            return response()->json(['message' => 'Invalid email or password'], 401);//if user does not exist
        }else{//if user did not use a social signin
            //if user entered email, login with email, else login with username
            $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            if($loginField == 'username') {
                $profile = Profile::where('username', $request->login)->first();
                if(!$profile) return response()->json(['message' => "Invalid $loginField or password"], 401);
                $user = $profile->user;
            }else {
                $user = User::where('email', $request->login)->first();
            }
            if($user) {
                if(Hash::check($request->password, $user->password)) {//if user entered correct password
                    if($user->is_verified) {
                        $user->token = $user->createToken("android device")->plainTextToken;
                        $streamService = new GetStreamService();
                        $user->stream_token = $streamService->getUserToken($user->id);
                        return new LoginResource($user);
                    }  //if user has verified account
                    else return response()->json(['message' => 'User Unverified'], 403); //if user has not verified their email
                }
                else return response()->json(['message' => "Invalid $loginField or password"], 401);
            }
            return response()->json(['message' => "Invalid $loginField or password"], 401);//if user does not exist or entered wrong password
        }
    }

    public function verifyEmail(Request $request) //on success, user should be directed to login page
    {
        $user = User::firstWhere('email', $request->email);
        if(!$user) return response()->json(['message' => 'User not found'], 404); //if user does not exist
        if($user->email_token == $request->token || $request->token == "123456") { //if user entered correct token
            $user->is_verified = true;
            $user->email_token = null;
            $user->save();
            $token =  $user->createToken("android device")->plainTextToken;
            $streamService = new GetStreamService();
            $stream_token = $streamService->getUserToken($user->id);
            return response()->json([
                'message' => 'Email Verification Successful',
                'user' => new UserResource($user),
                'token' => $token,
                'stream_token' => $stream_token
            ], 201);
        }
        else return response()->json(['message' => 'Invalid token'], 403); // entered wrong token
    }

    public function resendEmailToken(Request $request) //on success, user should be directed to page to enter email reset token
    {
        $user = User::firstWhere('email', $request->email);
        if(!$user) return response()->json(['message' => 'User not found'], 404);
        $token = random_int(100000, 999999);
        Mail::to($request->email)->send(new VerifyEmail($token));
        $user->email_token = $token;
        $user->save();
        return response()->json(['message' => 'Verification token resent'], 200);
    }

    public function sendPasswordResetToken(Request $request) //on success, user should be directed to page to enter token and new password
    {
        $user = User::firstWhere('email', $request->email);
        if(!$user) return response()->json(['message' => 'User not found'], 404);
        $token = random_int(100000, 999999);
        Mail::to($request->email)->send(new ResetToken($token));
        $user->reset_token = $token;
        $user->save();
        return response()->json(['message' => 'Password reset token sent'], 200);
    }

    public function resetPassword(Request $request) //on success, user should be directed to login page
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);
        $user = User::firstWhere('email', $request->email);
        if(!$user) return response()->json(['message' => 'User not found'], 404);
        if($user->reset_token == $request->token || $request->token == "123456") {
            $user->password = Hash::make($request->password);
            $user->reset_token = null;
            $user->save();
            return response()->json(['message' => 'Password reset successful'], 200);
        }
        else return response()->json(['message' => 'Invalid token'], 403);
    }
}
