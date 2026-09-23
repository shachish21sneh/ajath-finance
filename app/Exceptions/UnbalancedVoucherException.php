<?php

namespace App\Exceptions;

use Exception;

class UnbalancedVoucherException extends Exception
{
    public function __construct(string $message = 'Debit and Credit amounts must be equal to maintain accounting integrity.')
    {
        parent::__construct($message);
    }
}
