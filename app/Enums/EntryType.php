<?php

namespace App\Enums;

enum EntryType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function short(): string
    {
        return $this === self::DEBIT ? 'Dr' : 'Cr';
    }
}
