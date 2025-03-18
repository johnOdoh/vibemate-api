<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CommentReplyController;
use App\Http\Controllers\EntryCommentController;
use App\Http\Controllers\EntryCommentReplyController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-token', [AuthController::class, 'resendEmailToken']);
    Route::post('/send-reset-token', [AuthController::class, 'sendPasswordResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

ROute::group(['middleware' => 'auth:sanctum'], function () {

    Route::patch('competition/start/{competition}', [CompetitionController::class, 'start']);
    Route::patch('competition/end/{competition}', [CompetitionController::class, 'end']);

    Route::post('entry/like/{entry}', [EntryController::class, 'like']);
    Route::delete('entry/unlike/{entry}', [EntryController::class, 'unlike']);

    Route::get('post/getTopHashtags', [PostController::class, 'getTopHashtags']);
    Route::get('post/search/{query}', [PostController::class, 'searchArtist']);
    Route::post('post/like/{post}', [PostController::class, 'like']);
    Route::post('post/test', [PostController::class, 'test']);
    Route::delete('post/unlike/{post}', [PostController::class, 'unlike']);

    Route::post('profile/check-username', [ProfileController::class, 'checkUsername']);

    Route::post('user/follow/{id}', [UserController::class, 'follow']);
    Route::post('user/like/{id}', [UserController::class, 'like']);
    Route::delete('user/unfollow/{id}', [UserController::class, 'unfollow']);
    Route::delete('user/unlike/{id}', [UserController::class, 'unlike']);
    Route::delete('user/logout', [UserController::class, 'logout']);

    Route::post('ad/increment-views/{id}', [AdController::class, 'incrementViews']);
    Route::post('ad/increment-clicks/{id}', [AdController::class, 'incrementClicks']);

    Route::resource('comment', CommentController::class);
    Route::resource('competition', CompetitionController::class);
    Route::resource('entry', EntryController::class);
    Route::resource('entry-comment', EntryCommentController::class);
    Route::resource('entry-comment-reply', EntryCommentReplyController::class);
    Route::resource('post', PostController::class);
    Route::resource('profile', ProfileController::class);
    Route::resource('reply', CommentReplyController::class);
    Route::resource('user', UserController::class);
});
