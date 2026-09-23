<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = 'Insufficient inventory stock to complete this transaction.')
    {
        parent::__construct($message);
    }
}
