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
        $admin = User::where('email', 'admin@fuzurra.com')->first();
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

        // Test show view renders GST Tax Computation Breakdown with HSN
        $showRes = $this->get('/sales/' . $invoice->id);
        $showRes->assertStatus(200);
        $showRes->assertSee('GST Tax Computation Breakdown');
        $showRes->assertSee('HSN/SAC');
        $showRes->assertSee('Taxable');
        $showRes->assertSee('Total');

        // Check Base Measurement Unit is rendered with quantity
        $unitSymbol = $product->unit?->symbol ?: ($product->unit?->name ?: '');
        if ($unitSymbol) {
            $showRes->assertSee($unitSymbol);
        }
    }

    public function test_invoice_views_display_base_measurement_unit_and_api(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        // Verify product search API returns unit_symbol
        $apiRes = $this->getJson('/api/products/search?type=sales&limit=5');
        $apiRes->assertStatus(200);
        $json = $apiRes->json();
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('unit_symbol', $json[0]);

        // Verify sales create page has unit symbol binding
        $createRes = $this->get('/sales/create');
        $createRes->assertStatus(200);
        $createRes->assertSee('item.unit_symbol');

        // Verify purchases create page has unit symbol binding
        $purchCreateRes = $this->get('/purchases/create');
        $purchCreateRes->assertStatus(200);
        $purchCreateRes->assertSee('item.unit_symbol');
    }

    public function test_sales_invoice_with_custom_shipping_address_does_not_create_master_customer(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $customer = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->first();
        $product = Product::where('company_id', $company->id)->where('item_type', 'goods')->first();

        $initialCustomerCount = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->count();

        $customShipName = 'Apex Tech Logistics Site - Plot 50';
        $customShipAddress = 'Plot 50, Sector 18, Udyog Vihar';
        $customShipCity = 'Gurugram';
        $customShipState = 'Haryana';
        $customShipStateCode = '06';
        $customShipPincode = '122015';

        $response = $this->post('/sales', [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 1000.00,
            'shipping_name' => $customShipName,
            'shipping_phone' => '+91 98990 00000',
            'shipping_email' => 'site@apextech.com',
            'shipping_gstin' => '06AABCA1234F1Z8',
            'shipping_address' => $customShipAddress,
            'shipping_city' => $customShipCity,
            'shipping_state' => $customShipState,
            'shipping_state_code' => $customShipStateCode,
            'shipping_pincode' => $customShipPincode,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $invoice = SalesInvoice::latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($customShipName, $invoice->shipping_name);
        $this->assertEquals($customShipAddress, $invoice->shipping_address);
        $this->assertEquals($customShipCity, $invoice->shipping_city);
        $this->assertEquals($customShipState, $invoice->shipping_state);
        $this->assertTrue($invoice->hasCustomShippingAddress());

        // Ensure NO new customer/ledger was added in masters
        $afterCustomerCount = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->count();
        $this->assertEquals($initialCustomerCount, $afterCustomerCount);

        // Ensure show view renders Shipped To / Consignee Details with custom details
        $showRes = $this->get('/sales/' . $invoice->id);
        $showRes->assertStatus(200);
        $showRes->assertSee('Shipped To / Consignee Details:');
        $showRes->assertSee($customShipName);
        $showRes->assertSee($customShipAddress);
        $showRes->assertSee($customShipCity);
    }

    public function test_sales_invoice_without_shipping_address_shows_billing_address_in_ship_to(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $customer = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->first();
        $product = Product::where('company_id', $company->id)->where('item_type', 'goods')->first();

        $response = $this->post('/sales', [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 500.00,
            'shipping_name' => null,
            'shipping_address' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 1,
                    'unit_price' => 500.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $invoice = SalesInvoice::latest('id')->first();
        $this->assertFalse($invoice->hasCustomShippingAddress());

        // Show view should display Shipped To / Consignee Details using customer billing details
        $showRes = $this->get('/sales/' . $invoice->id);
        $showRes->assertStatus(200);
        $showRes->assertSee('Shipped To / Consignee Details:');
        $showRes->assertSee($customer->name);
    }
}
