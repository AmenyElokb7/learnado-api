<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Repositories\Payment\PaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PaymentController extends Controller
{
    protected $payments;

    public function __construct(PaymentRepository $payments)
    {
        $this->payments = $payments;
    }
    /**
     * Handle the incoming request
     * @param Request $request
     * @return JsonResponse
     */

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->all();
        try {
            $user = Auth::user();
            $course_ids = $data['course_ids'];

            $course_ids_array = array_map(function($item) {
                return $item['id'];
            }, $course_ids);
            $course_ids_array = array_map('intval', $course_ids_array);
            $session = $this->payments->createCheckoutSession($user, $course_ids_array);
            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
