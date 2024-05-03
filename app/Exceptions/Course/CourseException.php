<?php

namespace App\Exceptions\Course;

use App\Traits\ErrorResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CourseException extends Exception
{
    use ErrorResponse;

    public function __construct($message)
    {
        $message = __('messages.' . $message);
        parent::__construct($message);
    }

    public function report()
    {
        Log::error($this->getMessage());
    }

    public function render($request)
    {
        return $this->returnErrorResponse(__('course_exception'), ResponseAlias::HTTP_UNAUTHORIZED);
    }


}
