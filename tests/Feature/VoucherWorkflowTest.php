<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Voucher;
use Tests\TestCase;

class VoucherWorkflowTest extends TestCase
{
    public function test_voucher_index_renders_for_authenticated_accountant(): void
    {
        $accountant = User::where('email', 'accountant@ajath.com')->first();
        $this->actingAs($accountant);

        $response = $this->get('/vouchers');
        $response->assertStatus(200);
        $response->assertSee('Accounting Vouchers Register');
    }

    public function test_can_post_payment_voucher_with_balanced_entries(): void
    {
        $admin = User::where('email', 'admin@ajath.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $cash = Ledger::where('company_id', $company->id)->where('party_type', 'cash')->first();
        $supplier = Ledger::where('company_id', $company->id)->where('party_type', 'supplier')->first();

        $vNo = 'PMT-T-' . uniqid();

        $response = $this->post('/vouchers', [
            'voucher_type' => 'PAYMENT',
            'voucher_no' => $vNo,
            'voucher_date' => now()->toDateString(),
            'party_ledger_id' => $supplier->id,
            'payment_mode' => 'cash',
            'narration' => 'Cash paid to supplier for raw materials',
            'items' => [
                ['ledger_id' => $supplier->id, 'entry_type' => 'debit', 'amount' => 5000.00, 'narration' => 'Payment for supplies'],
                ['ledger_id' => $cash->id, 'entry_type' => 'credit', 'amount' => 5000.00, 'narration' => 'Cash disbursed'],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $voucher = Voucher::where('voucher_no', $vNo)->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(5000.00, $voucher->total_amount);
        $response->assertRedirect(route('vouchers.show', $voucher->id));
    }
}
