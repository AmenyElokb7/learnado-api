<?php

namespace App\Repositories\Statistics;

use App\Enum\TeachingTypeEnum;
use App\Enum\UserRoleEnum;
use App\Helpers\QueryConfig;
use App\Mail\CourseAssignedMail;
use App\Mail\GoogleMeetConfirmation;
use App\Mail\SendCertificateMail;
use App\Mail\sendSubscriptionMail;
use App\Models\Attestation;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\Invoice;
use App\Models\Language;
use App\Models\LearningPath;
use App\Models\Media;
use App\Models\Payment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\PaginationParams;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StatsticsRepository
{

    public static function getUserStatistics() : array
    {
        $user = Auth::user();
        $enrolledCourses = $user->subscribedCourses()->count();
        $enrolledLearningPaths = $user->subscribedLearningPaths()->count();
        $completedCourses = $user->subscribedCourses()->wherePivot('is_completed', 1)->count();
        $completedLearningPaths = $user->subscribedLearningPaths()->wherePivot('is_completed', 1)->count();
        $certificates = $user->certificates()->count();
        $attestations = $user->attestations()->count();
        $userId = $user->id;
        // take user_id from payment then get from invoice table the same payments id and get the total price
        $payments = Payment::where('user_id', $userId)->get();
        // total price for each month
        $totalPricePerMonth = $payments->flatMap(function ($payment) {
            return $payment->invoices()->get();
        })->groupBy(function ($invoice) {
            return $invoice->created_at->format('F');
        })->map(function ($invoices) {
            return $invoices->sum('total');
        });
        $months = collect([
            'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
        ]);
        $totalPricePerMonth = $months->mapWithKeys(function ($month) use ($totalPricePerMonth) {
            return [$month => $totalPricePerMonth->get($month, 0)];
        });
        return [
            'enrolled_courses' => $enrolledCourses,
            'completed_courses' => $completedCourses,
            'enrolled_learning_paths' => $enrolledLearningPaths,
            'completed_learning_paths' => $completedLearningPaths,
            'attestations' => $attestations,
            'certificates' => $certificates,
            'total_price_per_month' => $totalPricePerMonth
        ];
    }

    /**
     * Get statistics for facilitator
     * @return array
     */
    public static function getFacilitatorStatistics() : array
    {
        $user = Auth::user();
        $privateCourses = Course::where('facilitator_id', $user->id)->where('is_public', 0)->count();
        $publicCourses = Course::where('facilitator_id', $user->id)->where('is_public', 1)->count();
        $facilitatorCourses = Course::where('facilitator_id', $user->id)->get();
        $enrolledStudents = 0;
        foreach ($facilitatorCourses as $course) {
            $enrolledStudents += $course->subscribers()->count();
        }
        $userId = $user->id;
        $invoices = Invoice::all();
        $filteredInvoices = $invoices->map(function ($invoice) {
            $items = collect(json_decode($invoice->items, true));
            $courseItems = $items->filter(function ($item) {
                return isset($item['type']) && $item['type'] === "Course" && isset($item['id']);
            });
            $learningPathItems = $items->filter(function ($item) {
                return isset($item['type']) && $item['type'] === "Learning Path" && isset($item['id']);
            });
            $invoice->course_items = $courseItems->values();
            $invoice->learning_path_items = $learningPathItems->values();
            return $invoice;
        });
        $filteredInvoices = $filteredInvoices->filter(function ($invoice) {
            return count($invoice->course_items) > 0 || count($invoice->learning_path_items) > 0;
        });
        $courseIds = $filteredInvoices->flatMap(function ($invoice) {
            return $invoice->course_items;
        })->pluck('id')->filter();

        $learningPathIds = $filteredInvoices->flatMap(function ($invoice) {
            return $invoice->learning_path_items;
        })->pluck('id')->filter();

        $courses = Course::whereIn('id', $courseIds)->where('facilitator_id', $userId)->get()->keyBy('id');

        $learningPaths = LearningPath::whereIn('id', $learningPathIds)->get()->keyBy('id');

        $totalCourseFee = $filteredInvoices->flatMap(function ($invoice) use ($courses) {
            return $invoice->course_items->filter(function ($item) use ($courses) {
                return $courses->has($item['id']);
            });
        })->sum(function ($item) {
            $price = $item['price'];
            if (isset($item['discount'])) {
                $price -= ($price * $item['discount'] / 100);
            }
            return $price * 0.3;
        });

        $totalLearningPathFee = $filteredInvoices->flatMap(function ($invoice) use ($learningPaths, $userId) {
            return $invoice->learning_path_items->flatMap(function ($item) use ($learningPaths, $userId) {
                if ($learningPaths->has($item['id'])) {
                    $learningPath = $learningPaths->get($item['id']);
                    $coursesInLearningPath = $learningPath->courses()->where('facilitator_id', $userId)->get();
                    return $coursesInLearningPath->map(function ($course) {
                        $price = $course->price;
                        if (isset($course->discount)) {
                            $price -= ($price * $course->discount / 100);
                        }
                        return $price * 0.3;
                    });
                }
                return collect();
            });
        })->sum();
        $totalFee = $totalCourseFee + $totalLearningPathFee;
        $totalFee = round($totalFee, 2);
        return [
            'private_courses' => $privateCourses,
            'public_courses' => $publicCourses,
            'enrolled_students' => $enrolledStudents,
            'total_fee' => $totalFee,
        ];
    }

    /**
     * Get statistics for admin
     * @return array
     * @throws Exception
     */
    public static function getAdminStatistics() : array
    {
        $users = User::count();
        $courses = Course::count();
        $learningPaths = LearningPath::count();
        $certificates = CourseCertificate::count();
        $attestations = Attestation::count();
        $categories= Category::count();
        $languages = Language::count();
        $income = Invoice::sum('total');
        $income = round($income, 2);

        return [
            'users' => $users,
            'courses' => $courses,
            'learning_paths' => $learningPaths,
            'certificates' => $certificates,
            'attestations' => $attestations,
            'income' => $income,
            'categories' => $categories,
            'languages' => $languages
        ];
    }

    /**
     * Get statistics for designer
     * @return array
     */
    public static function getDesignerStatistics() : array
    {
        $user = Auth::user();
        $privateCourses =   Course::where('added_by', $user->id)->where('is_public', 0)->count();
        $publicCourses = Course::where('added_by', $user->id)->where('is_public', 1)->count();
        $enrolledStudentsInPrivateCourses = 0;
        $enrolledStudentsInPublicCourses = 0;
        $courses = Course::where('added_by', $user->id)->get();
        foreach ($courses as $course) {
            if ($course->is_public) {
                $enrolledStudentsInPublicCourses += $course->subscribers()->count();
            } else {
                $enrolledStudentsInPrivateCourses += $course->subscribers()->count();
            }
        }
        $enrolledStudentsInPrivateLearningPaths = 0;
        $enrolledStudentsInPublicLearningPaths = 0;
        $learningPaths = LearningPath::where('added_by', $user->id)->get();
        foreach ($learningPaths as $learningPath) {
            if ($learningPath->is_public) {
                $enrolledStudentsInPublicLearningPaths += $learningPath->subscribedUsersLearningPath()->count();
            } else {
                $enrolledStudentsInPrivateLearningPaths += $learningPath->subscribedUsersLearningPath()->count();
            }
        }
        $userId = $user->id;
        $invoices = Invoice::all();
        $filteredInvoices = $invoices->map(function ($invoice) {
            $items = collect(json_decode($invoice->items, true)); // Decode as array
            $courseItems = $items->filter(function ($item) {
                return isset($item['type']) && $item['type'] === "Course" && isset($item['id']);
            });
            $learningPathItems = $items->filter(function ($item) {
                return isset($item['type']) && $item['type'] === "Learning Path" && isset($item['id']);
            });
            $invoice->course_items = $courseItems->values();
            $invoice->learning_path_items = $learningPathItems->values();
            return $invoice;
        });
        $filteredInvoices = $filteredInvoices->filter(function ($invoice) {
            return count($invoice->course_items) > 0 || count($invoice->learning_path_items) > 0;
        });
        $courseIds = $filteredInvoices->flatMap(function ($invoice) {
            return $invoice->course_items;
        })->pluck('id')->filter();

        $learningPathIds = $filteredInvoices->flatMap(function ($invoice) {
            return $invoice->learning_path_items;
        })->pluck('id')->filter();

        $courses = Course::whereIn('id', $courseIds)->where('added_by', $userId)->get()->keyBy('id');
        $learningPaths = LearningPath::whereIn('id', $learningPathIds)->where('added_by', $userId)->get()->keyBy('id');
        $totalCoursePrice = $filteredInvoices->flatMap(function ($invoice) use ($courses) {
            return $invoice->course_items->filter(function ($item) use ($courses) {
                return $courses->has($item['id']);
            });
        })->sum(function ($item) {
            $price = $item['price'];
            if (isset($item['discount'])) {
                $price -= ($price * $item['discount'] / 100);
            }
            return $price;
        });
        $totalLearningPathPrice = $filteredInvoices->flatMap(function ($invoice) use ($learningPaths) {
            return $invoice->learning_path_items->filter(function ($item) use ($learningPaths) {
                return $learningPaths->has($item['id']);
            });
        })->sum(function ($item) {
            return $item['price'];
        });
        $totalPrice = $totalCoursePrice + $totalLearningPathPrice;
        $privateLearningPaths = LearningPath::where('added_by', $user->id)->where('is_public', 0)->count();
        $publicLearningPaths = LearningPath::where('added_by', $user->id)->where('is_public', 1)->count();
        $totalPrice = round($totalPrice, 2);
        return [
            'private_courses' => $privateCourses,
            'public_courses' => $publicCourses,
            'private_learning_paths' => $privateLearningPaths,
            'public_learning_paths' => $publicLearningPaths,
            'enrolled_students_in_private_courses' => $enrolledStudentsInPrivateCourses,
            'enrolled_students_in_public_courses' => $enrolledStudentsInPublicCourses,
            'enrolled_students_in_private_learning_paths' => $enrolledStudentsInPrivateLearningPaths,
            'enrolled_students_in_public_learning_paths' => $enrolledStudentsInPublicLearningPaths,
            'total_price' => $totalPrice,
        ];
    }

    /**
     * Get statistics for guest
     * @return array
     */

    public static function getGuestStatistics() : array
    {
        $courses = Course::where('is_public', 1)->where('is_active', 1)->where('is_offline',0)->count();
        $instructors = User::where('role', UserRoleEnum::FACILITATOR->value)->count();
        $onlineCourses = Course::where('is_public', 1)->where('is_active', 1)->where('is_offline',0)->where('teaching_type', TeachingTypeEnum::ONLINE->value)->count();
        $enrolledStudentsCourses = Course::where('is_public', 1)->where('is_active', 1)->where('is_offline',0)->get()->sum(function ($course) {
            return $course->subscribers()->count();
        });
        $enrolledStudentsLearningPaths = LearningPath::where('is_public', 1)->where('is_active', 1)->get()->sum(function ($learningPath) {
            return $learningPath->subscribedUsersLearningPath()->count();
        });
        $totalEnrolledStudents = $enrolledStudentsCourses + $enrolledStudentsLearningPaths;
        return [
            'courses' => $courses,
            'instructors' => $instructors,
            'online_courses' => $onlineCourses,
            'students' => $totalEnrolledStudents
        ];
    }
}
