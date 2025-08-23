<?php

namespace App\Exceptions;

use Exception;

class ExceptionAeropost extends Exception
{
    protected $internalCode;

    public function __construct($message = '', $internalCode = 0, $code = 0, ?Exception $previous = null)
    {
        $this->internalCode = $internalCode;
        parent::__construct($message, $code, $previous);
    }

    public function getInternalCode()
    {
        return $this->internalCode;
    }
}
