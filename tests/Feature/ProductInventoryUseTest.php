<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductInventoryComponent;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Services\InvoicingService;
use Tests\TestCase;

class ProductInventoryUseTest extends TestCase
{
    protected User $admin;
    protected Company $company;
    protected FinancialYear $fy;
    protected Ledger $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($this->admin);
        $this->company = Company::first();
        $this->fy = FinancialYear::first();
        $this->customer = Ledger::customers()->where('company_id', $this->company->id)->first() ?? Ledger::first();
    }

    public function test_can_create_product_with_inventory_use_components(): void
    {
        $unit = Unit::first();

        // 1. Create two raw material items
        $mat1 = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Raw Solar Inverter Unit',
            'item_type' => 'goods',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'opening_stock' => 50,
            'current_stock' => 50,
            'unit_id' => $unit?->id,
            'is_active' => true,
        ]);

        $mat2 = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Raw Lithium Battery Pack',
            'item_type' => 'goods',
            'purchase_price' => 4000,
            'selling_price' => 6000,
            'opening_stock' => 100,
            'current_stock' => 100,
            'unit_id' => $unit?->id,
            'is_active' => true,
        ]);

        // 2. Create parent product with Inventory Use enabled
        $response = $this->post(route('products.store'), [
            'name' => 'Complete Solar Power Kit 5KW',
            'item_type' => 'goods',
            'sku' => 'SOLAR-KIT-5KW',
            'purchase_price' => 13000,
            'selling_price' => 20000,
            'opening_stock' => 10,
            'reorder_level' => 2,
            'has_inventory_components' => 1,
            'components' => [
                [
                    'component_product_id' => $mat1->id,
                    'name' => 'Custom Inverter 5KW',
                    'hsn_code' => '850440',
                    'gst_rate' => 18,
                    'unit_price' => 5000,
                    'quantity' => 1,
                ],
                [
                    'component_product_id' => $mat2->id,
                    'name' => 'Lithium Pack 48V',
                    'hsn_code' => '850760',
                    'gst_rate' => 18,
                    'unit_price' => 4000,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));

        $kit = Product::where('name', 'Complete Solar Power Kit 5KW')->first();
        $this->assertNotNull($kit);
        $this->assertTrue((bool)$kit->has_inventory_components);
        $this->assertCount(2, $kit->inventoryComponents);

        $comp1 = $kit->inventoryComponents()->where('component_product_id', $mat1->id)->first();
        $this->assertNotNull($comp1);
        $this->assertEquals('Custom Inverter 5KW', $comp1->name);
        $this->assertEquals(1, (float)$comp1->quantity);

        $comp2 = $kit->inventoryComponents()->where('component_product_id', $mat2->id)->first();
        $this->assertNotNull($comp2);
        $this->assertEquals(2, (float)$comp2->quantity);

        // 3. Test update components
        $updateResponse = $this->put(route('products.update', $kit->id), [
            'name' => 'Complete Solar Power Kit 5KW Pro',
            'item_type' => 'goods',
            'purchase_price' => 17000,
            'selling_price' => 25000,
            'has_inventory_components' => 1,
            'components' => [
                [
                    'component_product_id' => $mat1->id,
                    'name' => 'Custom Inverter 5KW Pro',
                    'hsn_code' => '85044090',
                    'gst_rate' => 18,
                    'unit_price' => 5000,
                    'quantity' => 1,
                ],
                [
                    'component_product_id' => $mat2->id,
                    'name' => 'Lithium Pack 48V High-Capacity',
                    'hsn_code' => '850760',
                    'gst_rate' => 18,
                    'unit_price' => 4000,
                    'quantity' => 3, // updated from 2 to 3
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('products.index'));
        $kit->refresh();
        $this->assertEquals('Complete Solar Power Kit 5KW Pro', $kit->name);
        $this->assertCount(2, $kit->inventoryComponents);
        $updatedComp2 = $kit->inventoryComponents()->where('component_product_id', $mat2->id)->first();
        $this->assertEquals(3, (float)$updatedComp2->quantity);

        // Clean up
        $kit->inventoryComponents()->delete();
        $kit->delete();
        $mat1->delete();
        $mat2->delete();
    }

    public function test_selling_kit_product_automatically_deducts_component_inventory(): void
    {
        $unit = Unit::first();

        // 1. Create component items with initial stock
        $componentA = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Kit Sub Component A',
            'item_type' => 'goods',
            'purchase_price' => 200,
            'selling_price' => 350,
            'opening_stock' => 50,
            'current_stock' => 50,
            'unit_id' => $unit?->id,
            'is_active' => true,
        ]);

        $componentB = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Kit Sub Component B',
            'item_type' => 'goods',
            'purchase_price' => 100,
            'selling_price' => 150,
            'opening_stock' => 80,
            'current_stock' => 80,
            'unit_id' => $unit?->id,
            'is_active' => true,
        ]);

        // 2. Create Kit Product with components (1x A, 2x B)
        $kit = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Bundle Master Kit X',
            'item_type' => 'goods',
            'purchase_price' => 400,
            'selling_price' => 800,
            'opening_stock' => 20,
            'current_stock' => 20,
            'unit_id' => $unit?->id,
            'has_inventory_components' => true,
            'is_active' => true,
        ]);

        $kit->inventoryComponents()->create([
            'company_id' => $this->company->id,
            'component_product_id' => $componentA->id,
            'name' => 'Kit Sub Component A',
            'quantity' => 1.00,
            'unit_price' => 200.00,
            'gst_rate' => 18.00,
        ]);

        $kit->inventoryComponents()->create([
            'company_id' => $this->company->id,
            'component_product_id' => $componentB->id,
            'name' => 'Kit Sub Component B',
            'quantity' => 2.00,
            'unit_price' => 100.00,
            'gst_rate' => 18.00,
        ]);

        // 3. Sell 5 units of Bundle Master Kit X
        $invoicingService = app(InvoicingService::class);
        $invoice = $invoicingService->createSalesInvoice([
            'company_id' => $this->company->id,
            'financial_year_id' => $this->fy->id,
            'customer_ledger_id' => $this->customer->id,
            'invoice_type' => 'tax_invoice',
            'invoice_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 4720,
        ], [
            [
                'product_id' => $kit->id,
                'description' => 'Bundle Master Kit X',
                'quantity' => 5,
                'unit_price' => 800,
                'gst_rate' => 18,
            ]
        ]);

        $this->assertNotNull($invoice);

        // 4. Assert stock was deducted for Parent Kit (20 - 5 = 15)
        $this->assertEquals(15, (float)$kit->fresh()->current_stock);

        // 5. Assert stock was automatically deducted for Component A (50 - 5*1 = 45)
        $this->assertEquals(45, (float)$componentA->fresh()->current_stock);

        // 6. Assert stock was automatically deducted for Component B (80 - 5*2 = 70)
        $this->assertEquals(70, (float)$componentB->fresh()->current_stock);

        // 7. Verify stock movement records exist
        $movementsA = StockMovement::where('product_id', $componentA->id)->where('movement_type', 'outward')->get();
        $this->assertNotEmpty($movementsA);
        $this->assertEquals(5, (float)$movementsA->sum('quantity'));

        $movementsB = StockMovement::where('product_id', $componentB->id)->where('movement_type', 'outward')->get();
        $this->assertNotEmpty($movementsB);
        $this->assertEquals(10, (float)$movementsB->sum('quantity'));

        // Clean up
        $kit->inventoryComponents()->delete();
        $kit->stockMovements()->delete();
        $componentA->stockMovements()->delete();
        $componentB->stockMovements()->delete();
        $kit->delete();
        $componentA->delete();
        $componentB->delete();
    }

    public function test_ui_shows_inventory_use_section_and_badges(): void
    {
        // 1. Create page has the Inventory Use toggle
        $createRes = $this->get(route('products.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('inventory_use_toggle');
        $createRes->assertSee('Inventory Use (Kit / Bundle / BOM Components)');
        $createRes->assertSee('components_table');
        $createRes->assertSee('Add Item');
        $createRes->assertSee('searchable_component_picker');

        // 2. Product index shows components
        $indexRes = $this->get(route('products.index'));
        $indexRes->assertStatus(200);

        // 3. Sales create has components preview logic
        $salesRes = $this->get(route('sales.create'));
        $salesRes->assertStatus(200);
        $salesRes->assertSee('Auto-Deducted Components');
    }
}
