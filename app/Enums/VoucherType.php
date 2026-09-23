<?php

namespace App\Enums;

enum VoucherType: string
{
    case CONTRA = 'CONTRA';
    case PAYMENT = 'PAYMENT';
    case RECEIPT = 'RECEIPT';
    case JOURNAL = 'JOURNAL';
    case SALES = 'SALES';
    case PURCHASE = 'PURCHASE';
    case DEBIT_NOTE = 'DEBIT_NOTE';
    case CREDIT_NOTE = 'CREDIT_NOTE';

    public function shortcut(): string
    {
        return match ($this) {
            self::CONTRA => 'F4',
            self::PAYMENT => 'F5',
            self::RECEIPT => 'F6',
            self::JOURNAL => 'F7',
            self::SALES => 'F8',
            self::PURCHASE => 'F9',
            self::DEBIT_NOTE => 'Alt+F5',
            self::CREDIT_NOTE => 'Alt+F6',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CONTRA => 'Contra Voucher',
            self::PAYMENT => 'Payment Voucher',
            self::RECEIPT => 'Receipt Voucher',
            self::JOURNAL => 'Journal Voucher',
            self::SALES => 'Sales Voucher',
            self::PURCHASE => 'Purchase Voucher',
            self::DEBIT_NOTE => 'Debit Note (Purchase Return)',
            self::CREDIT_NOTE => 'Credit Note (Sales Return)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CONTRA => 'bg-info text-dark',
            self::PAYMENT => 'bg-danger text-white',
            self::RECEIPT => 'bg-success text-white',
            self::JOURNAL => 'bg-secondary text-white',
            self::SALES => 'bg-primary text-white',
            self::PURCHASE => 'bg-warning text-dark',
            self::DEBIT_NOTE => 'bg-danger-subtle text-danger',
            self::CREDIT_NOTE => 'bg-success-subtle text-success',
        };
    }
}
