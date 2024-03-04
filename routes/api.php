<?php

use App\Http\Controllers\Api\Admin\AdminRegisterController;
use App\Http\Controllers\Api\Admin\CreateUserAccountController;
use App\Http\Controllers\Api\Admin\DeleteUserAccountController;
use App\Http\Controllers\Api\Admin\GetAdminByEmailController;
use App\Http\Controllers\Api\Admin\IndexAdminsController;
use App\Http\Controllers\Api\Admin\SuspendUserAccountController;
use App\Http\Controllers\Api\Admin\UpdateUserAccountController;
use App\Http\Controllers\Api\Admin\ValidateAccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\User\GetUserByEmailController;
use App\Http\Controllers\Api\User\IndexUsersController;
use App\Http\Controllers\Api\User\SetPasswordController;
use App\Http\Controllers\Api\User\UserRegisterController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/login', AuthController::class)->name('login');
Route::post('/register/user', UserRegisterController::class);
Route::post('/register/admin', AdminRegisterController::class);
Route::post('/users/password/set', SetPasswordController::class);

// Protected routes for admin and user
Route::middleware('auth:User,admin')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::post('/refresh-token', TokenController::class);
});

// Protected routes for auth user and is admin
Route::middleware(['auth:User,admin', 'isAdmin'])->group(function () {
    Route::patch('/update-user-account/{id}', UpdateUserAccountController::class);
});

// Protected routes for only admin
Route::middleware(['auth:admin', 'isAdmin'])->prefix(
    'admin'
)->group(function () {
    Route::post('/validate-user-account', ValidateAccountController::class);
    Route::post('/create-user', CreateUserAccountController::class);
    Route::post('/suspend-account', SuspendUserAccountController::class);
    Route::delete('/delete-user-account', DeleteUserAccountController::class);
    Route::get('/admins', IndexAdminsController::class);
    Route::get('/users', IndexUsersController::class);

});

Route::get('/user/{email}', GetUserByEmailController::class)->middleware('auth:User');
Route::get('/admin/{email}', GetAdminByEmailController::class)->middleware('auth:admin');


