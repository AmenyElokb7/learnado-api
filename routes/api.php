<?php

use App\Http\Controllers\Api\Admin\AdminRegisterController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\User\UserRegisterController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/login', AuthController::class)->name('login');
Route::post('/register/user', UserRegisterController::class);
Route::post('/register/admin', AdminRegisterController::class);

// Protected routes for admin and user
Route::middleware('auth:User,admin')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::post('/refresh-token', TokenController::class);
});
