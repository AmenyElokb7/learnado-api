<?php

namespace App\Repositories\Payment;

use App\Helpers\QueryConfig;
use App\Mail\InvoiceMail;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;


class PaymentRepository
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * @param User $user
     * @param array $courseIds
     * @return object
     * @throws ApiErrorException
     */

    public final function createCheckoutSession(User $user, array $courseIds) : object
    {
        $courses = Course::findMany($courseIds);
        $lineItems = $courses->map(function ($course) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $course->title],
                    'unit_amount' => $course->price * 100,
                ],
                'quantity' => 1,
            ];
        });

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems->toArray(),
            'mode' => 'payment',
            'success_url' => config('app.frontend_url'),
            'cancel_url' => config('app.frontend_url'),
        ]);

        $payment = new Payment([
            'user_id' => $user->id,
            'stripe_payment_id' => $session->id,
            'amount' => $courses->sum('price'),
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


        $this->sendInvoicePDF($invoice->id);

        $user = $payment->user;
        $courses = $user->cart;
        $uniqueCourseIds = $courses->pluck('id')->unique();

        // the user is subscribed to the courses
        $user->subscribedCourses()->syncWithoutDetaching($uniqueCourseIds);

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
    public function sendInvoicePDF($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $pdf = PDF::loadView('invoices.invoice', compact('invoice'));
        // Generate PDF content
        $pdfContent = $pdf->output();
        // Send the PDF via email
        Mail::to($invoice->email)->send(new InvoiceMail($invoice, $pdfContent));
        return response()->json(['message' => 'Invoice sent successfully.']);
    }

    public static function indexUserInvoices(QueryConfig $queryConfig) : LengthAwarePaginator|Collection
    {
        $user= Auth::user();
        $invoiceQuery = Invoice::where('email', $user->email)->newQuery();
        $invoiceQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        return $queryConfig->getPaginated()
            ? $invoiceQuery->paginate($queryConfig->getPerPage())
            : $invoiceQuery->get();
    }
}
