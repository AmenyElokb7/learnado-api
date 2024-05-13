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


        // Create line items for courses
        $courseLineItems = $courses->map(function ($course) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $course->title,
                    ],
                    'unit_amount' => $course->price * 100,
                ],
                'quantity' => 1,
            ];
        });

        // Create line items for learning paths
        $learningPathLineItems = $learningPaths->map(function ($learningPath) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $learningPath->title,
                    ],
                    'unit_amount' => $learningPath->price * 100,
                ],
                'quantity' => 1,
            ];
        });

        // Combine all line items into a single array and if the courseLineItems is empty, then only return the learningPathLineItems and vice versa
        $allLineItems = array_merge($courseLineItems->toArray(), $learningPathLineItems->toArray());



        // Create the Stripe checkout session
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $allLineItems,
            'mode' => 'payment',
            'success_url' => config('app.frontend_url'),
            'cancel_url' => config('app.frontend_url'),
        ]);

        $totalAmount = $courses->sum('price') + $learningPaths->sum('price');

        // Record the payment in the database
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
        // create invoice
        $invoice = Invoice::create([
            'username' => $payment->user->first_name . ' ' . $payment->user->last_name,
            'email' => $payment->user->email,
            'seller_name' => Invoice::SELLER_NAME,
            'seller_email' => Invoice::SELLER_EMAIL,
            'items' => json_encode($payment->user->cart->map(function ($course) {
                return ['name' => $course->title, 'price' => $course->price];
            })->toArray()),
            'total' => $payment->amount,
            'payment_id' => $payment->id,
        ]);


        $this->generateInvoicePDF($invoice->id);

        $user = $payment->user;
        $courses = $user->cart;
        $uniqueCourseIds = $courses->pluck('id')->unique();

        // the user is subscribed to the courses
        $user->subscribedCourses()->syncWithoutDetaching($uniqueCourseIds);
        // the user is subscribed to the learning paths
        $user->subscribedLearningPaths()->syncWithoutDetaching($user->learningPathInCart->pluck('id')->unique());
        // send subscription email
        foreach ($courses as $course) {
            Mail::to($user->email)->send(new sendSubscriptionMail(true, $course->title, $course->id));
        }
        // send subscription email of the learning paths
        foreach ($user->learningPathInCart as $learningPath) {
            Mail::to($user->email)->send(new sendSubscriptionMail(false, $learningPath->title, $learningPath->id));
        }
        // Clear the user's cart
        $user->cart()->detach();
    }
    /**
     * @throws Exception
     */
    public final function handleWebhook($event)
    {
        $paymentIntentId = $event->data->object->id;
        $this->handleCompletedSession($paymentIntentId);
    }
    public function generateInvoicePDF($invoiceId)
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

    public function downloadInvoicePDF($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $pdf = PDF::loadView('invoices.invoice', compact('invoice'));
        return response($pdf->output(), ResponseAlias::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoiceId . '.pdf"'
        ]);
    }


}
