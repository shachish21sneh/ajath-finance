<?php

namespace App\Enums;

enum PartyType: string
{
    case NONE = 'none';
    case CUSTOMER = 'customer';
    case SUPPLIER = 'supplier';
    case BANK = 'bank';
    case CASH = 'cash';
    case EMPLOYEE = 'employee';
}
