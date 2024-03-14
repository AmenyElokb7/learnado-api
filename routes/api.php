<?php

use App\Http\Controllers\Api\Admin\CreateAccountController;
use App\Http\Controllers\Api\Admin\DeleteAccountController;
use App\Http\Controllers\Api\Admin\SuspendUserAccountController;
use App\Http\Controllers\Api\Admin\UpdateAccountController;
use App\Http\Controllers\Api\Admin\ValidateAccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SendPasswordResetMailController;
use App\Http\Controllers\Api\Auth\SetPasswordController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\Category\CreateCategoryController;
use App\Http\Controllers\Api\Category\DeleteCategoryController;
use App\Http\Controllers\Api\Category\IndexCategoriesController;
use App\Http\Controllers\Api\Course\CreateCourseController;
use App\Http\Controllers\Api\Course\DeleteCourseController;
use App\Http\Controllers\Api\Course\GetCourseByIdController;
use App\Http\Controllers\Api\Course\GetSubscribedUsersByCourseController;
use App\Http\Controllers\Api\Course\IndexCoursesController;
use App\Http\Controllers\Api\Course\UpdateCourseController;
use App\Http\Controllers\Api\Language\CreateLanguageController;
use App\Http\Controllers\Api\Language\DeleteLanguageController;
use App\Http\Controllers\Api\Language\IndexLanguagesController;
use App\Http\Controllers\Api\LearningPath\CreateLearningController;
use App\Http\Controllers\Api\Quiz\DeleteQuizController;
use App\Http\Controllers\Api\Quiz\UpdateQuizController;
use App\Http\Controllers\Api\Step\CreateStepController;
use App\Http\Controllers\Api\Step\DeleteStepController;
use App\Http\Controllers\Api\Step\GetCourseStepsController;
use App\Http\Controllers\Api\Step\UpdateStepController;
use App\Http\Controllers\Api\User\GetUserByIdController;
use App\Http\Controllers\Api\User\IndexUsersController;
use App\Http\Controllers\Api\User\UpdateProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', AuthController::class)->name('login');
Route::post('/register', RegisterController::class);
Route::post('/password-set', SetPasswordController::class);
Route::post('/send-password-reset-mail', SendPasswordResetMailController::class);
Route::post('/password-reset', ResetPasswordController::class);


Route::middleware('auth:user')->group(function () {

    Route::post('/logout', LogoutController::class);
    Route::post('/refresh-token', TokenController::class);
    Route::patch('/update-profile', UpdateProfileController::class);

    Route::middleware('admin')->prefix(
        'admin'
    )->group(function () {
        Route::post('/validate-user-account/{id}', ValidateAccountController::class);
        Route::post('/create-user', CreateAccountController::class);
        Route::post('/suspend-account/{id}', SuspendUserAccountController::class);
        Route::delete('/delete-user-account/{id}', DeleteAccountController::class);
        Route::get('/users', IndexUsersController::class);
        Route::patch('/update-user-account/{id}', UpdateAccountController::class);
        Route::get('/user/{id}', GetUserByIdController::class);
        Route::post('/create-language', CreateLanguageController::class);
        Route::delete('/delete-language/{id}', DeleteLanguageController::class);
        Route::get('/languages', IndexLanguagesController::class);
        Route::post('/create-category', CreateCategoryController::class);
        Route::delete('/delete-category/{id}', DeleteCategoryController::class);
        Route::get('/categories', IndexCategoriesController::class);

    });

    Route::middleware('designer')->prefix(
        'designer'
    )->group(function () {
        Route::post('/create-course', CreateCourseController::class);
        Route::delete('/delete-course/{id}', DeleteCourseController::class);
        Route::patch('/update-course/{id}', UpdateCourseController::class);
        Route::get('/subscribed-users/{id}', GetSubscribedUsersByCourseController::class);
        Route::post('/create-step/{course_id}', CreateStepController::class);
        Route::patch('/update-steps/{step_id}', UpdateStepController::class);
        Route::patch('/update-quiz/{step_id}', UpdateQuizController::class);
        Route::get('/courses/{course_id}/steps', GetCourseStepsController::class);
        Route::delete('/delete-step/{step_id}', DeleteStepController::class);
        Route::delete('/delete-quiz/{step_id}', DeleteQuizController::class);
        Route::post('/create-learning-path', CreateLearningController::class);


    });


});
Route::get('/courses', IndexCoursesController::class);
Route::get('course/{id}', GetCourseByIdController::class);


// middleware refresh-token & access token




