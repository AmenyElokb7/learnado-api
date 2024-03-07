<?php

use App\Http\Controllers\Api\Admin\CreateAccountController;
use App\Http\Controllers\Api\Admin\DeleteAccountController;
use App\Http\Controllers\Api\Admin\IndexAdminsController;
use App\Http\Controllers\Api\Admin\SuspendUserAccountController;
use App\Http\Controllers\Api\Admin\UpdateAccountController;
use App\Http\Controllers\Api\Admin\ValidateAccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SendPasswordResetMailController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\Course\AffectCourseController;
use App\Http\Controllers\Api\Course\CreateCourseController;
use App\Http\Controllers\Api\User\GetUserByIdController;
use App\Http\Controllers\Api\User\IndexUsersController;
use App\Http\Controllers\Api\User\SetPasswordController;
use App\Http\Controllers\Api\User\UpdateProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', AuthController::class)->name('login');
Route::post('/register', RegisterController::class);
Route::post('/users/password-set', SetPasswordController::class);
Route::post('/users/send-password-reset-mail', SendPasswordResetMailController::class);
Route::post('/users/password-reset', ResetPasswordController::class);


// Protected routes for admin and user
Route::middleware('auth:user')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::post('/refresh-token', TokenController::class);
    Route::patch('/update-profile', UpdateProfileController::class);
});

// Protected routes for only admin
Route::middleware(['auth:user', 'admin'])->prefix(
    'admin'
)->group(function () {
    Route::post('/validate-user-account/{id}', ValidateAccountController::class);
    Route::post('/create-user', CreateAccountController::class);
    Route::post('/suspend-account/{id}', SuspendUserAccountController::class);
    Route::delete('/delete-user-account/{id}', DeleteAccountController::class);
    Route::get('/users', IndexUsersController::class);
    Route::patch('/update-user-account/{id}', UpdateAccountController::class);
    Route::get('/user/{id}', GetUserByIdController::class);
});


// Courses routes for concepteur pédagogique
Route::middleware(['auth:user', 'concepteur'])->prefix(
    'concepteur'
)->group(function () {
    Route::post('/create-course', CreateCourseController::class);
    Route::post('/affect-course/{course_id}/{facilitator_id}', AffectCourseController::class);
});

