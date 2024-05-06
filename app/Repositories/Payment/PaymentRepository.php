<?php

namespace App\Repositories\Payment;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Checkout\Session as StripeSession;


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
            'status' => 'pending',
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
            throw new \Exception('Payment not found');
        }
        if ($payment->status === 'completed') {
            throw new \Exception('Payment already completed');
        }

        $user = $payment->user;

        $payment->status = 'completed';
        $payment->save();

        // Subscribe the user to the courses that he paid for
        $courses = $payment->courses;
        $user->subscribedCourses()->attach($courses);

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

}
