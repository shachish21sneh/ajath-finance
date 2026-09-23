<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use Tests\TestCase;

class SalesAndPurchaseTest extends TestCase
{
    public function test_can_create_tax_invoice_and_deduct_inventory(): void
    {
        $admin = User::where('email', 'admin@ajath.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $customer = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->first();
        $product = Product::where('company_id', $company->id)->where('item_type', 'goods')->first();

        $initialStock = (float) $product->current_stock;

        $response = $this->post('/sales', [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 3500.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 2,
                    'unit_price' => 3500.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $invoice = SalesInvoice::latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($customer->id, $invoice->customer_ledger_id);

        $product->refresh();
        $this->assertEquals($initialStock - 2, (float) $product->current_stock);
    }
}
