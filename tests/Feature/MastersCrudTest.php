<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\UqcMaster;
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
        $tax = TaxMaster::where('company_id', $this->company->id)->first();

        // 1. Index
        $res = $this->get(route('stock-groups.index'));
        $res->assertStatus(200);
        $res->assertSee('Stock Groups');
        $res->assertSee('HSN / SAC');
        $res->assertSee('GST Rate');

        // 2. Store via standard form with HSN and GST Rate
        $postRes = $this->post(route('stock-groups.store'), [
            'name' => 'Test Solar Inverters',
            'parent_id' => null,
            'hsn_code' => '850440',
            'tax_master_id' => $tax?->id,
        ]);
        $postRes->assertRedirect(route('stock-groups.index'));

        $group = StockGroup::where('name', 'Test Solar Inverters')->first();
        $this->assertNotNull($group);
        $this->assertEquals($this->company->id, $group->company_id);
        $this->assertEquals('850440', $group->hsn_code);
        $this->assertEquals($tax?->id, $group->tax_master_id);

        // 3. Store via AJAX with HSN and GST Rate
        $ajaxRes = $this->postJson(route('stock-groups.store'), [
            'name' => 'Test Lithium Subgroup',
            'parent_id' => $group->id,
            'hsn_code' => '850760',
            'tax_master_id' => $tax?->id,
        ]);
        $ajaxRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test Lithium Subgroup',
                    'hsn_code' => '850760',
                    'tax_master_id' => $tax?->id,
                ],
            ]);

        $subGroup = StockGroup::where('name', 'Test Lithium Subgroup')->first();
        $this->assertNotNull($subGroup);
        $this->assertEquals('850760', $subGroup->hsn_code);

        // 4. Update
        $updateRes = $this->put(route('stock-groups.update', $group->id), [
            'name' => 'Updated Solar Inverters',
            'hsn_code' => '85044090',
            'tax_master_id' => $tax?->id,
        ]);
        $updateRes->assertRedirect(route('stock-groups.index'));
        $this->assertEquals('Updated Solar Inverters', $group->fresh()->name);
        $this->assertEquals('85044090', $group->fresh()->hsn_code);

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
        $this->assertEquals('KWH', $unit->fresh()->symbol);

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

    public function test_uqc_master_crud(): void
    {
        // 1. Index
        $res = $this->get(route('uqc.index'));
        $res->assertStatus(200);
        $res->assertSee('GST UQC Codes');
        $res->assertSee('NOS');
        $res->assertSee('KGS');
        $res->assertSee('BAG');

        // 2. Search
        $searchRes = $this->get(route('uqc.index', ['search' => 'Bags']));
        $searchRes->assertStatus(200);
        $searchRes->assertSee('BAG');
        $searchRes->assertSee('Bags');

        // 3. Store custom UQC (auto upper-cased)
        $storeRes = $this->post(route('uqc.store'), [
            'code' => 'tst',
            'name' => 'Testing Custom Code',
            'is_active' => 1,
        ]);
        $storeRes->assertRedirect(route('uqc.index'));

        $customUqc = UqcMaster::where('code', 'TST')->first();
        $this->assertNotNull($customUqc);
        $this->assertEquals('Testing Custom Code', $customUqc->name);
        $this->assertTrue((bool)$customUqc->is_active);

        // 4. Auto-linking when Unit is created with UQC code
        $unitRes = $this->post(route('units.store'), [
            'name' => 'Standard Bags',
            'symbol' => 'BAG',
            'decimal_places' => 0,
        ]);
        $unitRes->assertRedirect(route('units.index'));

        $bagUnit = Unit::where('symbol', 'BAG')->first();
        $this->assertNotNull($bagUnit);
        $bagUqc = UqcMaster::where('code', 'BAG')->first();
        $this->assertEquals($bagUqc->id, $bagUnit->uqc_id);

        // 5. Cannot delete UQC when in use
        $delBagRes = $this->delete(route('uqc.destroy', $bagUqc->id));
        $delBagRes->assertSessionHas('error');
        $this->assertNotNull(UqcMaster::find($bagUqc->id));

        // Clean up unit
        $bagUnit->delete();

        // 6. Update custom UQC
        $updateRes = $this->put(route('uqc.update', $customUqc->id), [
            'code' => 'TST2',
            'name' => 'Updated Custom Code',
            'is_active' => 0,
        ]);
        $updateRes->assertRedirect(route('uqc.index'));
        $this->assertEquals('TST2', $customUqc->fresh()->code);
        $this->assertFalse((bool)$customUqc->fresh()->is_active);

        // 7. Delete custom UQC
        $delRes = $this->delete(route('uqc.destroy', $customUqc->id));
        $delRes->assertRedirect(route('uqc.index'));
        $this->assertNull(UqcMaster::find($customUqc->id));
    }
}
