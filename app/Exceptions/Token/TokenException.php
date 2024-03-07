<?php

namespace App\Exceptions\Token;

use App\Traits\ErrorResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TokenException extends Exception
{
    use ErrorResponse;

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = __('messages.token_invalid');
        parent::__construct($message, $code, $previous);
    }

    public function report()
    {
        Log::error($this->getMessage());
    }

    public function render($request)
    {
        return $this->returnErrorResponse(__('token_invalid'), ResponseAlias::HTTP_UNAUTHORIZED);
    }
}
