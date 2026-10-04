<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\User;
use Tests\TestCase;

class MastersCrudTest extends TestCase
{
    protected User $admin;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($this->admin);
        $this->company = Company::first();
    }

    public function test_stock_group_crud_and_ajax(): void
    {
        // 1. Index
        $res = $this->get(route('stock-groups.index'));
        $res->assertStatus(200);
        $res->assertSee('Stock Groups');

        // 2. Store via standard form
        $postRes = $this->post(route('stock-groups.store'), [
            'name' => 'Test Solar Inverters',
            'parent_id' => null,
        ]);
        $postRes->assertRedirect(route('stock-groups.index'));

        $group = StockGroup::where('name', 'Test Solar Inverters')->first();
        $this->assertNotNull($group);
        $this->assertEquals($this->company->id, $group->company_id);

        // 3. Store via AJAX
        $ajaxRes = $this->postJson(route('stock-groups.store'), [
            'name' => 'Test Lithium Subgroup',
            'parent_id' => $group->id,
        ]);
        $ajaxRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test Lithium Subgroup',
                ],
            ]);

        $subGroup = StockGroup::where('name', 'Test Lithium Subgroup')->first();
        $this->assertNotNull($subGroup);

        // 4. Update
        $updateRes = $this->put(route('stock-groups.update', $group->id), [
            'name' => 'Updated Solar Inverters',
        ]);
        $updateRes->assertRedirect(route('stock-groups.index'));
        $this->assertEquals('Updated Solar Inverters', $group->fresh()->name);

        // 5. Delete
        $delRes = $this->delete(route('stock-groups.destroy', $subGroup->id));
        $delRes->assertRedirect(route('stock-groups.index'));
        $this->assertNull(StockGroup::find($subGroup->id));

        $group->delete();
    }

    public function test_unit_crud_and_ajax(): void
    {
        // 1. Index
        $res = $this->get(route('units.index'));
        $res->assertStatus(200);
        $res->assertSee('Base Measurement Units');

        // 2. Store via standard form
        $postRes = $this->post(route('units.store'), [
            'name' => 'Kilowatt Hour',
            'symbol' => 'KWH',
            'decimal_places' => 2,
        ]);
        $postRes->assertRedirect(route('units.index'));

        $unit = Unit::where('symbol', 'KWH')->first();
        $this->assertNotNull($unit);
        $this->assertEquals(2, $unit->decimal_places);

        // 3. Store via AJAX
        $ajaxRes = $this->postJson(route('units.store'), [
            'name' => 'Megawatt',
            'symbol' => 'MW',
            'decimal_places' => 3,
        ]);
        $ajaxRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Megawatt',
                    'symbol' => 'MW',
                    'display' => 'Megawatt (MW)',
                ],
            ]);

        $mwUnit = Unit::where('symbol', 'MW')->first();
        $this->assertNotNull($mwUnit);

        // 4. Update
        $updateRes = $this->put(route('units.update', $unit->id), [
            'name' => 'KiloWatt-Hour',
            'symbol' => 'kWh',
            'decimal_places' => 2,
        ]);
        $updateRes->assertRedirect(route('units.index'));
        $this->assertEquals('kWh', $unit->fresh()->symbol);

        // 5. Delete
        $delRes = $this->delete(route('units.destroy', $mwUnit->id));
        $delRes->assertRedirect(route('units.index'));
        $this->assertNull(Unit::find($mwUnit->id));

        $unit->delete();
    }

    public function test_tax_master_crud_and_ajax(): void
    {
        // 1. Index
        $res = $this->get(route('taxes.index'));
        $res->assertStatus(200);
        $res->assertSee('Applicable GST Rates');

        // 2. Store via standard form
        $postRes = $this->post(route('taxes.store'), [
            'name' => 'GST 40% Test',
            'rate' => 40.0,
            'cess_rate' => 2.0,
        ]);
        $postRes->assertRedirect(route('taxes.index'));

        $tax = TaxMaster::where('name', 'GST 40% Test')->first();
        $this->assertNotNull($tax);
        $this->assertEquals(20.0, (float)$tax->cgst_rate);
        $this->assertEquals(20.0, (float)$tax->sgst_rate);
        $this->assertEquals(40.0, (float)$tax->igst_rate);

        // 3. Store via AJAX
        $ajaxRes = $this->postJson(route('taxes.store'), [
            'name' => 'GST 3% Test Gold',
            'rate' => 3.0,
            'cess_rate' => 0.0,
        ]);
        $ajaxRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'GST 3% Test Gold',
                    'rate' => 3.0,
                ],
            ]);

        $goldTax = TaxMaster::where('name', 'GST 3% Test Gold')->first();
        $this->assertNotNull($goldTax);

        // 4. Update
        $updateRes = $this->put(route('taxes.update', $tax->id), [
            'name' => 'GST 42% Super',
            'rate' => 42.0,
            'cess_rate' => 0.0,
        ]);
        $updateRes->assertRedirect(route('taxes.index'));
        $this->assertEquals(42.0, (float)$tax->fresh()->rate);

        // 5. Delete
        $delRes = $this->delete(route('taxes.destroy', $goldTax->id));
        $delRes->assertRedirect(route('taxes.index'));
        $this->assertNull(TaxMaster::find($goldTax->id));

        $tax->delete();
    }

    public function test_product_forms_have_quick_create_options(): void
    {
        $createRes = $this->get(route('products.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('quickAddStockGroupModal');
        $createRes->assertSee('quickAddUnitModal');
        $createRes->assertSee('quickAddTaxModal');
        $createRes->assertSee('fa-circle-plus');

        $product = \App\Models\Product::first();
        if ($product) {
            $editRes = $this->get(route('products.edit', $product->id));
            $editRes->assertStatus(200);
            $editRes->assertSee('quickAddStockGroupModal');
            $editRes->assertSee('quickAddUnitModal');
            $editRes->assertSee('quickAddTaxModal');
            $editRes->assertSee('fa-circle-plus');
        }
    }
}
