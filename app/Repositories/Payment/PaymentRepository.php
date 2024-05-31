<?php

namespace App\Repositories\Payment;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
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
            'success_url' => config('app.frontend_url') . '/success',
            'cancel_url' => config('app.frontend_url') . '/failure',
        ]);

        $payment = new Payment([
            'user_id' => $user->id,
            'stripe_payment_id' => $session->id,
            'amount' => $courses->sum('price'),
            'status' => 'pending',
        ]);
        $payment->save();

        foreach ($courses as $course) {
            $course->subscribers()->sync($user->id);
        }

        return $session;
    }

}
