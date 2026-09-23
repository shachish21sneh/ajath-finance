<?php

namespace App\Enums;

enum LedgerNature: string
{
    case ASSET = 'ASSET';
    case LIABILITY = 'LIABILITY';
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';

    public function normalBalance(): string
    {
        return match ($this) {
            self::ASSET, self::EXPENSE => 'Dr',
            self::LIABILITY, self::INCOME => 'Cr',
        };
    }
}
