<?php

use App\Http\Controllers\Api\Admin\CreateAccountController;
use App\Http\Controllers\Api\Admin\DeleteAccountController;
use App\Http\Controllers\Api\Admin\IndexUsersController;
use App\Http\Controllers\Api\Admin\SuspendUserAccountController;
use App\Http\Controllers\Api\Admin\UpdateAccountController;
use App\Http\Controllers\Api\Admin\ValidateAccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\SendPasswordResetMailController;
use App\Http\Controllers\Api\Auth\SetPasswordController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\Category\CreateCategoryController;
use App\Http\Controllers\Api\Category\DeleteCategoryController;
use App\Http\Controllers\Api\Category\IndexCategoriesController;
use App\Http\Controllers\Api\Course\CourseSubscriptionController;
use App\Http\Controllers\Api\Course\CreateCourseController;
use App\Http\Controllers\Api\Course\DeleteCourseController;
use App\Http\Controllers\Api\Course\GetCourseByIdForDesignerController;
use App\Http\Controllers\Api\Course\GetCourseByIdForFacilitatorController;
use App\Http\Controllers\Api\Course\GetCourseByIdForUserController;
use App\Http\Controllers\Api\Course\IndexCoursesForDesignerController;
use App\Http\Controllers\Api\Course\IndexCoursesForFacilitator;
use App\Http\Controllers\Api\Course\IndexCoursesForUsersController;
use App\Http\Controllers\Api\Course\UpdateCourseController;
use App\Http\Controllers\Api\Language\CreateLanguageController;
use App\Http\Controllers\Api\Language\DeleteLanguageController;
use App\Http\Controllers\Api\Language\IndexLanguagesController;
use App\Http\Controllers\Api\LearningPath\CreateLearningController;
use App\Http\Controllers\Api\LearningPath\DeleteLearningPathController;
use App\Http\Controllers\Api\LearningPath\LearningPathSubscriptionController;
use App\Http\Controllers\Api\LearningPath\UpdateLearningPathController;
use App\Http\Controllers\Api\Quiz\DeleteLearningPathQuizController;
use App\Http\Controllers\Api\Quiz\DeleteStepQuizController;
use App\Http\Controllers\Api\Quiz\UpdateLearningPathQuizController;
use App\Http\Controllers\Api\Quiz\UpdateStepQuizController;
use App\Http\Controllers\Api\Step\CreateStepController;
use App\Http\Controllers\Api\Step\DeleteStepController;
use App\Http\Controllers\Api\Step\UpdateStepController;
use App\Http\Controllers\Api\User\GetUserByIdController;
use App\Http\Controllers\Api\User\IndexFacilitatorsController;
use App\Http\Controllers\Api\User\UpdateProfileController;
use App\Http\Controllers\Api\User\UserAnswersController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\Admin\RejectUserAccountController;

// Public routes
Route::post('/login', AuthController::class);
Route::post('/register', RegisterController::class);
Route::post('/password-set', SetPasswordController::class);
Route::post('/send-password-reset-mail', SendPasswordResetMailController::class);


Route::middleware('auth:user')->group(function () {

    Route::post('/logout', LogoutController::class);
    Route::patch('/update-profile', UpdateProfileController::class);
    Route::post('/refresh-token', TokenController::class)->middleware('refreshToken');

    Route::middleware('user')->group(function () {
        Route::post('/subscribe-learning-path/{id}', LearningPathSubscriptionController::class);
        Route::post('/subscribe-course/{id}', CourseSubscriptionController::class);
        Route::post('/quiz/{quiz_id}/submit', UserAnswersController::class)->middleware('subscribed');

    });

    Route::middleware('admin')->prefix(
        'admin'
    )->group(function () {
        Route::post('/validate-user-account/{id}', ValidateAccountController::class);
        Route::post('/create-user', CreateAccountController::class);
        Route::post('/suspend-account/{id}', SuspendUserAccountController::class);
        Route::post('/reject-user-account/{id}', RejectUserAccountController::class);
        Route::delete('/delete-user-account/{id}', DeleteAccountController::class);
        Route::get('/users', IndexUsersController::class);
        Route::get('/pending-users', \App\Http\Controllers\Api\Admin\IndexPendingUsersController::class);
        Route::get('/accepted-users', \App\Http\Controllers\Api\Admin\IndexAcceptedUsersController::class);
        Route::post('/update-user-account/{id}', UpdateAccountController::class);
        Route::get('/users/{id}', GetUserByIdController::class);
        Route::post('/create-language', CreateLanguageController::class);
        Route::delete('/delete-language/{id}', DeleteLanguageController::class);
        Route::post('/create-category', CreateCategoryController::class);
        Route::delete('/delete-category/{id}', DeleteCategoryController::class);
    });

    Route::middleware('designer')->prefix(
        'designer'
    )->group(function () {
        Route::post('/create-course', CreateCourseController::class);
        Route::delete('/delete-course/{id}', DeleteCourseController::class);
        Route::patch('/update-course/{id}', UpdateCourseController::class);
        Route::post('/create-step/{course_id}', CreateStepController::class);
        Route::patch('/update-steps/{step_id}', UpdateStepController::class);
        Route::patch('/update-step-quiz/{step_id}', UpdateStepQuizController::class);
        Route::delete('/delete-step/{step_id}', DeleteStepController::class);
        Route::delete('/delete-step-quiz/{step_id}', DeleteStepQuizController::class);
        Route::post('/create-learning-path', CreateLearningController::class);
        Route::patch('/update-learning-path/{id}', UpdateLearningPathController::class);
        Route::patch('/update-lp-quiz/{learning_path_id}', UpdateLearningPathQuizController::class);
        Route::delete('/delete-lp-quiz/{learning_path_id}', DeleteLearningPathQuizController::class);
        Route::delete('/delete-learning-path/{id}', DeleteLearningPathController::class);
        Route::get('/courses', IndexCoursesForDesignerController::class);
        Route::get('/users', \App\Http\Controllers\Api\Course\IndexUsersController::class);
        Route::get('courses/{id}', GetCourseByIdForDesignerController::class);

    });
    Route::middleware('facilitator')->prefix(
        'facilitator'
    )->group(function () {
        Route::get('/courses', IndexCoursesForFacilitator::class);
        Route::get('courses/{id}', GetCourseByIdForFacilitatorController::class);

    });

});

// Public routes for guests
Route::get('/courses', IndexCoursesForUsersController::class);
Route::get('courses/{id}', GetCourseByIdForUserController::class);
Route::get('/categories', IndexCategoriesController::class);
Route::get('/languages', IndexLanguagesController::class);
Route::get('/facilitators', IndexFacilitatorsController::class);




