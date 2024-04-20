<?php

namespace App\Exceptions;
use Exception;

class StructuredException extends \Exception
{
    protected $errors;

    public function __construct($errors, $code = 0, Exception $previous = null) {
        parent::__construct(json_encode(['message' => $errors]), $code, $previous);
        $this->errors = $errors;
    }

    public function getStructuredErrors() {
        return $this->errors;
    }
}
