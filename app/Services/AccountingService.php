<?php

namespace App\Services;

use App\Enums\EntryType;
use App\Enums\LedgerNature;
use App\Enums\VoucherType;
use App\Exceptions\UnbalancedVoucherException;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\Voucher;
use App\Models\VoucherItem;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create a double-entry accounting voucher and immediately post atomic ledger entries.
     *
     * @throws UnbalancedVoucherException
     */
    public function createVoucher(array $voucherData, array $items): Voucher
    {
        return DB::transaction(function () use ($voucherData, $items) {
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($items as $item) {
                $amount = (float) $item['amount'];
                if (($item['entry_type'] ?? 'debit') === 'debit' || ($item['entry_type'] ?? '') === EntryType::DEBIT->value) {
                    $totalDebit += $amount;
                } else {
                    $totalCredit += $amount;
                }
            }

            // Invariant: Total Debits must equal Total Credits
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new UnbalancedVoucherException(
                    "Accounting Invariant Violation: Debits (₹ " . number_format($totalDebit, 2) . 
                    ") do not match Credits (₹ " . number_format($totalCredit, 2) . ")"
                );
            }

            $voucherData['total_amount'] = $totalDebit;
            $voucher = Voucher::create($voucherData);

            foreach ($items as $index => $item) {
                VoucherItem::create([
                    'voucher_id' => $voucher->id,
                    'ledger_id' => $item['ledger_id'],
                    'entry_type' => $item['entry_type'],
                    'amount' => $item['amount'],
                    'narration' => $item['narration'] ?? null,
                    'line_order' => $index + 1,
                ]);
            }

            $this->postLedgerEntries($voucher);

            return $voucher;
        });
    }

    /**
     * Post ledger entries for an approved voucher and update ledger cached balances.
     */
    public function postLedgerEntries(Voucher $voucher): void
    {
        // First delete any previous ledger entries for this voucher (if re-posting)
        $this->reverseLedgerEntries($voucher);

        foreach ($voucher->items as $item) {
            $ledger = $item->ledger;
            $entryType = $item->entry_type instanceof EntryType ? $item->entry_type->value : $item->entry_type;

            LedgerEntry::create([
                'company_id' => $voucher->company_id,
                'financial_year_id' => $voucher->financial_year_id,
                'voucher_id' => $voucher->id,
                'ledger_id' => $item->ledger_id,
                'entry_type' => $entryType,
                'amount' => $item->amount,
                'balance_after' => 0.00, // recalculated below
                'entry_date' => $voucher->voucher_date,
                'narration' => $item->narration ?? $voucher->narration,
            ]);

            $this->recalculateLedgerBalance($ledger);
        }
    }

    /**
     * Reverse and clean up ledger postings for a voucher.
     */
    public function reverseLedgerEntries(Voucher $voucher): void
    {
        $affectedLedgerIds = $voucher->ledgerEntries()->pluck('ledger_id')->unique();
        $voucher->ledgerEntries()->delete();

        foreach ($affectedLedgerIds as $ledgerId) {
            $ledger = Ledger::find($ledgerId);
            if ($ledger) {
                $this->recalculateLedgerBalance($ledger);
            }
        }
    }

    /**
     * Recalculates current balance of a ledger based on opening balance and all posted ledger entries.
     */
    public function recalculateLedgerBalance(Ledger $ledger): float
    {
        $opening = (float) $ledger->opening_balance;
        if ($ledger->opening_balance_type === 'Cr') {
            $opening = -$opening;
        }

        $debits = (float) LedgerEntry::where('ledger_id', $ledger->id)
            ->where('entry_type', 'debit')
            ->sum('amount');

        $credits = (float) LedgerEntry::where('ledger_id', $ledger->id)
            ->where('entry_type', 'credit')
            ->sum('amount');

        // Net balance: Positive is Debit, Negative is Credit
        $netBalance = $opening + ($debits - $credits);

        $ledger->update(['current_balance' => $netBalance]);

        return $netBalance;
    }

    /**
     * Generate the next sequential voucher number for a company, FY, and voucher type.
     */
    public function getNextVoucherNumber(Company $company, FinancialYear $fy, VoucherType|string $type): string
    {
        $typeVal = $type instanceof VoucherType ? $type->value : $type;
        $prefix = match ($typeVal) {
            'CONTRA' => 'CNTR',
            'PAYMENT' => 'PMT',
            'RECEIPT' => 'RCPT',
            'JOURNAL' => 'JRNL',
            'SALES' => 'SLS',
            'PURCHASE' => 'PRCH',
            'DEBIT_NOTE' => 'DN',
            'CREDIT_NOTE' => 'CN',
            default => 'VCH',
        };

        $count = Voucher::where('company_id', $company->id)
            ->where('financial_year_id', $fy->id)
            ->where('voucher_type', $typeVal)
            ->count();

        $nextSeq = str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$nextSeq}";
    }
}
