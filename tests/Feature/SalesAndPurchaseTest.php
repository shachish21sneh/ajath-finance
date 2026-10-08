<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\PurchaseInvoice;
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

    public function test_can_edit_and_update_sales_invoice(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $customer = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->first();
        $product = Product::where('company_id', $company->id)->where('item_type', 'goods')->first();

        // Create an initial invoice
        $this->post('/sales', [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 1000.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 2,
                    'unit_price' => 500.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $invoice = SalesInvoice::latest('id')->first();
        $this->assertNotNull($invoice);

        // 1. Verify Edit button is visible on Index page
        $indexRes = $this->get('/sales');
        $indexRes->assertStatus(200);
        $indexRes->assertSee(route('sales.edit', $invoice->id));

        // 2. Verify Edit button is visible on Show page
        $showRes = $this->get('/sales/' . $invoice->id);
        $showRes->assertStatus(200);
        $showRes->assertSee(route('sales.edit', $invoice->id));
        $showRes->assertSee('Edit Invoice');

        // 3. Verify Edit page renders 200 OK
        $editRes = $this->get('/sales/' . $invoice->id . '/edit');
        $editRes->assertStatus(200);
        $editRes->assertSee('Edit GST Sales Tax Invoice: ' . $invoice->invoice_no);

        // 4. Update the invoice with new quantity & custom shipping address
        $stockBeforeUpdate = (float) $product->fresh()->current_stock;
        $updatedRes = $this->put('/sales/' . $invoice->id, [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'paid_amount' => 2500.00,
            'shipping_name' => 'Consignee Site B',
            'shipping_city' => 'Noida',
            'shipping_state' => 'Uttar Pradesh',
            'shipping_state_code' => '09',
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name . ' Updated',
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 3, // Increased by 1 from 2
                    'unit_price' => 700.00,
                    'discount_amount' => 50.00,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $updatedRes->assertRedirect(route('sales.show', $invoice->id));

        $invoice->refresh();
        $this->assertEquals('Consignee Site B', $invoice->shipping_name);
        $this->assertEquals('bank_transfer', $invoice->payment_method);
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals(3, (float) $invoice->items()->first()->quantity);

        // Net stock should have decreased by 1 additional unit
        $this->assertEquals($stockBeforeUpdate - 1, (float) $product->fresh()->current_stock);
    }

    public function test_can_edit_and_update_purchase_invoice(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $supplier = Ledger::where('company_id', $company->id)->where('party_type', 'supplier')->first();
        $product = Product::where('company_id', $company->id)->where('item_type', 'goods')->first();

        // Create an initial purchase bill
        $billNo = 'BILL-TEST-' . rand(1000, 9999);
        $this->post('/purchases', [
            'supplier_ledger_id' => $supplier->id,
            'bill_no' => $billNo,
            'bill_date' => now()->toDateString(),
            'paid_amount' => 500.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $purchase = PurchaseInvoice::latest('id')->first();
        $this->assertNotNull($purchase);

        // 1. Verify Edit button is visible on Index page
        $indexRes = $this->get('/purchases');
        $indexRes->assertStatus(200);
        $indexRes->assertSee(route('purchases.edit', $purchase->id));

        // 2. Verify Edit button is visible on Show page
        $showRes = $this->get('/purchases/' . $purchase->id);
        $showRes->assertStatus(200);
        $showRes->assertSee(route('purchases.edit', $purchase->id));
        $showRes->assertSee('Edit Bill');

        // 3. Verify Edit page renders 200 OK
        $editRes = $this->get('/purchases/' . $purchase->id . '/edit');
        $editRes->assertStatus(200);
        $editRes->assertSee('Edit Vendor Purchase Bill: ' . $purchase->bill_no);

        // 4. Update the bill with new quantity
        $stockBeforeUpdate = (float) $product->fresh()->current_stock;
        $updatedRes = $this->put('/purchases/' . $purchase->id, [
            'supplier_ledger_id' => $supplier->id,
            'bill_no' => $billNo,
            'bill_date' => now()->toDateString(),
            'paid_amount' => 800.00,
            'notes' => 'Updated GRN reference',
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 8, // Increased from 5 to 8 (+3 inward)
                    'unit_price' => 100.00,
                    'discount_amount' => 0,
                    'gst_rate' => 18.00,
                ]
            ]
        ]);

        $updatedRes->assertRedirect(route('purchases.show', $purchase->id));

        $purchase->refresh();
        $this->assertEquals('Updated GRN reference', $purchase->notes);
        $this->assertEquals(8, (float) $purchase->items()->first()->quantity);

        // Live stock should have increased by 3 additional units
        $this->assertEquals($stockBeforeUpdate + 3, (float) $product->fresh()->current_stock);
    }

    public function test_multiline_item_description_in_sales_and_purchase_invoices(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);
        $company = Company::first();

        // 1. Verify create form has textarea with @keydown.enter.stop
        $createRes = $this->get('/sales/create');
        $createRes->assertStatus(200);
        $createRes->assertSee('<textarea', false);
        $createRes->assertSee('@keydown.enter.stop', false);

        // 2. Create invoice with multiline description
        $customer = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->first();
        $product = Product::where('company_id', $company->id)->first();

        $multilineDesc = "Line 1: High efficiency monocrystalline module\nLine 2: Serial #SN-2026-998877\nLine 3: 10 Year Comprehensive Warranty";

        $postRes = $this->post('/sales', [
            'customer_ledger_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 500,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => $multilineDesc,
                    'hsn_code' => $product->hsn_code,
                    'quantity' => 2,
                    'unit_price' => 250,
                    'gst_rate' => 18,
                ]
            ]
        ]);

        $postRes->assertRedirect();
        $invoiceId = str_replace(url('/sales') . '/', '', $postRes->headers->get('Location'));
        $invoice = SalesInvoice::with('items')->find($invoiceId);
        $this->assertNotNull($invoice);

        $savedItem = $invoice->items->first();
        $this->assertStringContainsString("Line 2: Serial #SN-2026-998877", $savedItem->description);

        // 3. Verify show page displays multiline description with formatting
        $showRes = $this->get('/sales/' . $invoice->id);
        $showRes->assertStatus(200);
        $showRes->assertSee('Line 1: High efficiency monocrystalline module');
        $showRes->assertSee('Line 2: Serial #SN-2026-998877');
        $showRes->assertSee('Line 3: 10 Year Comprehensive Warranty');

        // 4. Verify Payment Settlement & Notes is displayed on view page with no-print class
        $showRes->assertSee('Payment Settlement & Notes', false);
        $showRes->assertSee('Screen view only');
        $showRes->assertSee('Cash');

        // 5. Verify Sales Index has Direct Print action button
        $indexRes = $this->get('/sales');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('title="Direct Print Invoice"', false);
        $indexRes->assertSee('printInvoiceDirect', false);

        // 6. Verify Purchases Index has Direct Print action button
        $purchIndexRes = $this->get('/purchases');
        $purchIndexRes->assertStatus(200);
        $purchIndexRes->assertSee('title="Direct Print Bill"', false);
        $purchIndexRes->assertSee('printInvoiceDirect', false);
    }
}

