<?php

namespace App\Exceptions\Token;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TokenException extends Exception
{

    public function __construct($message = 'Token error.', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function report()
    {
        Log::error($this->getMessage());
    }

    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], Response::HTTP_BAD_REQUEST);
    }
}
