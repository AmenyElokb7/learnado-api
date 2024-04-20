<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="Learnado API",
 *     version="1.0.0",
 *     description="This is a simple API for an e-learning application",
 *     @OA\Contact(
 *       email="support@learnado.com",
 *       name="Support Team"
 *     )
 *   ),
 *   @OA\Components(
 *     @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *       description="Enter JWT Bearer token **_only_**"
 *     )
 *   )
 * )
 */



class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
