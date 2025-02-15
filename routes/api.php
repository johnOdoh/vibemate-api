<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-token', [AuthController::class, 'resendEmailToken']);
    Route::post('/send-reset-token', [AuthController::class, 'sendPasswordResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

ROute::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', [UserController::class, 'user']);
    Route::post('/auth/signout', [UserController::class, 'signout']);

    Route::resource('profile', ProfileController::class);
});
