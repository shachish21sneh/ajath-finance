<?php

namespace Tests\Feature;

use App\Enums\VoucherType;
use App\Exceptions\UnbalancedVoucherException;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Services\AccountingService;
use App\Services\ReportService;
use Tests\TestCase;

class AccountingIntegrityTest extends TestCase
{
    public function test_unbalanced_voucher_throws_exception(): void
    {
        $this->expectException(UnbalancedVoucherException::class);

        $company = Company::first();
        $fy = FinancialYear::first();
        $ledgers = Ledger::where('company_id', $company->id)->take(2)->get();

        $accountingService = app(AccountingService::class);
        $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::JOURNAL,
            'voucher_no' => 'TEST-001',
            'voucher_date' => now()->toDateString(),
        ], [
            ['ledger_id' => $ledgers[0]->id, 'entry_type' => 'debit', 'amount' => 1000.00],
            ['ledger_id' => $ledgers[1]->id, 'entry_type' => 'credit', 'amount' => 950.00], // Unbalanced!
        ]);
    }

    public function test_balanced_voucher_posts_atomic_ledger_entries(): void
    {
        $company = Company::first();
        $fy = FinancialYear::first();
        $ledgers = Ledger::where('company_id', $company->id)->take(2)->get();

        $accountingService = app(AccountingService::class);
        $voucher = $accountingService->createVoucher([
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::JOURNAL,
            'voucher_no' => 'TEST-BALANCED-' . uniqid(),
            'voucher_date' => now()->toDateString(),
            'narration' => 'Balanced test journal entry',
        ], [
            ['ledger_id' => $ledgers[0]->id, 'entry_type' => 'debit', 'amount' => 2500.00],
            ['ledger_id' => $ledgers[1]->id, 'entry_type' => 'credit', 'amount' => 2500.00],
        ]);

        $this->assertNotNull($voucher->id);
        $this->assertTrue($voucher->isBalanced());
        $this->assertCount(2, $voucher->ledgerEntries);
    }
}
