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
use App\Http\Controllers\Api\Quiz\DeleteQuizController;
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
use \App\Http\Controllers\Api\User\UserProfileController;
use \App\Http\Controllers\Api\Category\GetCategoryByIdController;

// Public routes
Route::post('/login', AuthController::class);
Route::post('/register', RegisterController::class);
Route::post('/password-set', SetPasswordController::class);
Route::post('/send-password-reset-mail', SendPasswordResetMailController::class);
Route::post('/refresh-token', TokenController::class)->middleware('refreshToken');
Route::get('/certificates/download/{certificateId}', \App\Http\Controllers\Api\Course\DownloadCertificateController::class)->name('certificates.download');



Route::middleware('auth:user')->group(function () {

    Route::post('/logout', LogoutController::class);
    Route::post('/update-profile', UpdateProfileController::class);
    Route::get('/profile', UserProfileController::class);
    Route::get('/courses', IndexCoursesForUsersController::class);
    Route::get('/courses/{id}', GetCourseByIdForUserController::class);


    Route::middleware('user')->group(function () {
        Route::post('/subscribe-learning-path/{id}', LearningPathSubscriptionController::class);
        Route::post('/subscribe-course/{id}', CourseSubscriptionController::class);
        Route::post('/quiz/submit/{quiz_id}', UserAnswersController::class)->middleware('subscribed');
        Route::get('/enrolled-courses', \App\Http\Controllers\Api\Course\IndexEnrolledCoursesController::class);
        Route::get('/quiz/score/{quiz_id}', \App\Http\Controllers\Api\Quiz\GetUserScoreController::class);
        Route::get('/quiz-attempts', \App\Http\Controllers\Api\Quiz\IndexQuizAttemptsController::class);
        Route::post('/complete-course/{course_id}', \App\Http\Controllers\Api\Course\CompleteCourseController::class);
        Route::get('/course-certificate', \App\Http\Controllers\Api\Course\IndexCourseCertificatesController::class);
        Route::get('/quiz-scores', \App\Http\Controllers\Api\Quiz\IndexQuizScoresController::class);
        Route::get('/completed-courses', \App\Http\Controllers\Api\Course\IndexCompletedCoursesController::class);
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
        Route::post('/update-category/{id}', \App\Http\Controllers\Api\Category\UpdateCategoryController::class);
        Route::delete('/delete-category/{id}', DeleteCategoryController::class);
        Route::get('/notifications', \App\Http\Controllers\Api\Admin\AdminNotificationController::class);
    });

    Route::middleware('designer')->prefix(
        'designer'
    )->group(function () {
        Route::post('/create-course', CreateCourseController::class);
        Route::delete('/delete-course/{id}', DeleteCourseController::class);
        Route::post('/update-course/{id}', UpdateCourseController::class);
        Route::post('/create-step/{course_id}', CreateStepController::class);
        Route::post('/update-step/{step_id}', UpdateStepController::class);
        Route::post('/update-step-quiz/{step_id}', UpdateStepQuizController::class);
        Route::delete('/delete-step/{step_id}', DeleteStepController::class);
        Route::delete('/delete-quiz/{quiz_id}', DeleteQuizController::class);
        Route::post('/create-learning-path', CreateLearningController::class);
        Route::patch('/update-learning-path/{id}', UpdateLearningPathController::class);
        Route::patch('/update-lp-quiz/{learning_path_id}', UpdateLearningPathQuizController::class);
        Route::delete('/delete-lp-quiz/{learning_path_id}', DeleteLearningPathQuizController::class);
        Route::delete('/delete-learning-path/{id}', DeleteLearningPathController::class);
        Route::get('/courses', IndexCoursesForDesignerController::class);
        Route::get('/users', \App\Http\Controllers\Api\Course\IndexUsersController::class);
        Route::get('courses/{id}', GetCourseByIdForDesignerController::class);
        Route::delete('/delete-question/{question_id}', \App\Http\Controllers\Api\Quiz\DeleteQuestionController::class);
        Route::delete('/delete-answer/{answer_id}', \App\Http\Controllers\Api\Quiz\DeleteAnswerController::class);
        Route::post('/support-message', \App\Http\Controllers\Api\Message\SupportMessageController::class);



    });
    Route::middleware('facilitator')->prefix(
        'facilitator'
    )->group(function () {
        Route::get('/courses', IndexCoursesForFacilitator::class);
        Route::get('courses/{id}', GetCourseByIdForFacilitatorController::class);

    });

});


// Public routes for guests
Route::get('/guest-courses', \App\Http\Controllers\Api\Course\IndexCoursesForGuestController::class);
Route::get('/guest-courses/{id}', \App\Http\Controllers\Api\Course\GetCourseByIdForGuestController::class);
Route::get('/steps/{id}', \App\Http\Controllers\Api\Step\GetStepMediaByIdController::class);
Route::get('/categories', IndexCategoriesController::class);
Route::get('/categories-filter', \App\Http\Controllers\Api\Category\IndexCategoriesWithCoursesController::class);

Route::get('/categories/{id}', GetCategoryByIdController::class);
Route::get('/languages', IndexLanguagesController::class);
Route::get('/facilitators', IndexFacilitatorsController::class);



