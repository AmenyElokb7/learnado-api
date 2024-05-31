<?php

namespace App\Repositories\Payment;

use App\Helpers\QueryConfig;
use App\Mail\InvoiceMail;
use App\Mail\sendSubscriptionMail;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\LearningPath;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class PaymentRepository
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * @param User $user
     * @param array $itemIds
     * @return object
     * @throws ApiErrorException
     */

    public final function createCheckoutSession(User $user, array $itemIds) : object
    {

        $items = DB::table('cart')->whereIn('id', $itemIds)->get();
        $courseLineItems = [];
        $learningPathLineItems = [];
        $courses = $user->cart()->whereIn('course_id', $items->pluck('course_id'))->get();
        $learningPaths = $user->learningPathInCart()->whereIn('learning_path_id', $items->pluck('learning_path_id'))->get();
        $courseLineItems = $courses->map(function ($course) {
            $finalPrice = $course->price - ($course->price * ($course->discount / 100));
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $course->title,
                    ],
                    'unit_amount' =>  (int)($finalPrice * 100),
                ],
                'quantity' => 1,
            ];
        });
        $purchasedCoursesIds = $user->subscribedCourses->pluck('id');
        $authUserId = $user->id;
        $learningPathLineItems =  $learningPaths->map(function ($learningPath) use ($authUserId, $purchasedCoursesIds) {
            $coursesIds = $learningPath->courses->pluck('id');
            $purchasedCourses = $coursesIds->intersect($purchasedCoursesIds);

            // price - discount in percent of the course
            $totalPrice = $purchasedCourses->sum(function ($courseId) {
                $course = Course::find($courseId);
                return $course->price - ($course->price * $course->discount / 100);
            });
            $totalPrice = $learningPath->price - $totalPrice;
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $learningPath->title,
                    ],
                    'unit_amount' => $totalPrice * 100,
                ],
                'quantity' => 1,
            ];
        });
        $allLineItems = array_merge($courseLineItems->toArray(), $learningPathLineItems->toArray());
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $allLineItems,
            'mode' => 'payment',
            'success_url' => config('app.frontend_url'),
            'cancel_url' => config('app.frontend_url'),
        ]);
        $totalAmount = $courses->sum('price') + $learningPaths->sum('price');
        $payment = new Payment([
            'user_id' => $user->id,
            'stripe_payment_id' => $session->id,
            'amount' => $totalAmount,
            'status' => Payment::PENDING,
        ]);
        $payment->save();

        return $session;
    }

    /**
     * complete the payment
     * @param string $paymentIntentId
     * @throws Exception
     */
    public final function handleCompletedSession(string $paymentIntentId) : void
    {
        $payment = Payment::where('stripe_payment_id', $paymentIntentId)->first();
        if (!$payment) {
            throw new \Exception('payment_not_found');
        }
        if ($payment->status === Payment::COMPLETED) {
            throw new \Exception('payment_already_completed');
        }
        $payment->status = Payment::COMPLETED;
        $payment->save();
        $user = $payment->user;
        $cartCourses = $user->cart;
        $cartLearningPaths = $user->learningPathInCart;
        $courseItems = $cartCourses->map(function ($course) {
            $finalPrice = $course->price - ($course->price * ($course->discount / 100));
            return [
                'id' => $course->id,
                'name' => $course->title,
                'price' => $finalPrice,
                'type' => 'Course'
            ];
        });
        $learningPathItems = $cartLearningPaths->map(function ($learningPath) use ($user) {
            $purchasedCoursesIds = $user->subscribedCourses->pluck('id');
            $coursesIds = $learningPath->courses->pluck('id');
            $purchasedCourses = $coursesIds->intersect($purchasedCoursesIds);
            $totalPrice = $purchasedCourses->sum(function ($courseId) {
                $course = Course::find($courseId);
                return $course->price - ($course->price * $course->discount / 100);
            });
            $finalPrice = $learningPath->price - $totalPrice;
            return [
                'id' => $learningPath->id,
                'name' => $learningPath->title,
                'price' => $finalPrice,
                'type' => 'Learning Path'
            ];
        });
        if ($courseItems->isEmpty() && $learningPathItems->isEmpty()) {
            throw new \Exception('No items in cart');
        } elseif ($courseItems->isEmpty()) {
            $items = $learningPathItems->toArray();
        } elseif ($learningPathItems->isEmpty()) {
            $items = $courseItems->toArray();
        } else {
            $items = $courseItems->merge($learningPathItems)->toArray();
        }
        $totalAmount = collect($items)->sum('price');
        $invoice = Invoice::create([
            'username' => $payment->user->first_name . ' ' . $payment->user->last_name,
            'email' => $payment->user->email,
            'seller_name' => Invoice::SELLER_NAME,
            'seller_email' => Invoice::SELLER_EMAIL,
            'items' => json_encode($items),
            'total' => $totalAmount,
            'payment_id' => $payment->id,
        ]);
        $this->generateInvoicePDF($invoice->id);
        $courses = $user->cart;
        $uniqueCourseIds = $courses->pluck('id')->unique();
        $user->subscribedCourses()->syncWithoutDetaching($uniqueCourseIds);
        $user->subscribedLearningPaths()->syncWithoutDetaching($user->learningPathInCart->pluck('id')->unique());
        foreach ($courses as $course) {
            Mail::to($user->email)->send(new sendSubscriptionMail(true, $course->title, $course->id));
        }
        foreach ($user->learningPathInCart as $learningPath) {
            Mail::to($user->email)->send(new sendSubscriptionMail(false, $learningPath->title, $learningPath->id));
        }
        $user->cart()->detach();
    }

    /**
     * @param $event
     * @throws Exception
     */
    public final function handleWebhook($event) : void
    {
        $paymentIntentId = $event->data->object->id;
        $this->handleCompletedSession($paymentIntentId);
    }

    /**
     * @param $invoiceId
     * @return JsonResponse
     */
    public final function generateInvoicePDF($invoiceId): JsonResponse
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $pdf = PDF::loadView('invoices.invoice', compact('invoice'));
        // Generate PDF content
        $pdfContent = $pdf->output();
        // Send the PDF via email
        Mail::to($invoice->email)->send(new InvoiceMail($invoice, $pdfContent));
        return response()->json(['message' => 'Invoice sent successfully.']);
    }

    /**
     * index user invoices
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */
    public static function indexUserInvoices(QueryConfig $queryConfig) : LengthAwarePaginator|Collection
    {
        $user= Auth::user();
        $invoiceQuery = Invoice::where('email', $user->email)->newQuery();
        $invoiceQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        $invoiceQuery->select('id', 'created_at', 'total');
        return $queryConfig->getPaginated()
            ? $invoiceQuery->paginate($queryConfig->getPerPage())
            : $invoiceQuery->get();
    }

    /**
     * @param $invoiceId
     * @return Response
     */

    public final function downloadInvoicePDF($invoiceId) : Response
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $pdf = PDF::loadView('invoices.invoice', compact('invoice'));
        return response($pdf->output(), ResponseAlias::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoiceId . '.pdf"'
        ]);
    }


}
