<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CommentReplyController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-token', [AuthController::class, 'resendEmailToken']);
    Route::post('/send-reset-token', [AuthController::class, 'sendPasswordResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

ROute::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/auth/signout', [UserController::class, 'signout']);

    Route::get('post/getTopHashtags', [PostController::class, 'getTopHashtags']);
    Route::post('post/like/{post}', [PostController::class, 'like']);
    Route::post('post/unlike/{post}', [PostController::class, 'unlike']);

    Route::post('user/follow/{id}', [UserController::class, 'follow']);
    Route::post('user/unfollow/{id}', [UserController::class, 'unfollow']);

    Route::post('ad/increment-views/{id}', [AdController::class, 'incrementViews']);
    Route::post('ad/increment-clicks/{id}', [AdController::class, 'incrementClicks']);

    Route::resource('comment', CommentController::class);
    Route::resource('competition', CompetitionController::class);
    Route::resource('post', PostController::class);
    Route::resource('profile', ProfileController::class);
    Route::resource('reply', CommentReplyController::class);
    Route::resource('user', UserController::class);
});
