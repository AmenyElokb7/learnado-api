<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/add-to-cart/{course_id}",
 *     summary="Add a course to the authenticated user's cart",
 *     tags={"User Cart"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         description="ID of the course to add to the cart",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course added to cart successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="added_to_cart_successfully"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             )
 *         )
 *     )
 * )
 */

class AddToCartController extends Controller
{
    use ErrorResponse, SuccessResponse;
    public function __invoke($course_id): JsonResponse
    {
        try {
            CourseRepository::addToCart($course_id);
            return $this->returnSuccessResponse(__('added_to_cart_successfully'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
