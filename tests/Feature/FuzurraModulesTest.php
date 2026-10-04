<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FuzurraModulesTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'admin@fuzurra.com')->first();
    }

    public function test_battery_erp_module_and_serial_tracking(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/battery');
        $res->assertStatus(200);
        $res->assertSee('Battery ERP', false);
        $res->assertSee('FZ-512100-2026-0001', false);

        // Test API serial search
        $apiRes = $this->getJson('/battery/serials/FZ-512100-2026-0001');
        $apiRes->assertStatus(200);
        $data = $apiRes->json();
        $this->assertEquals('FZ-512100-2026-0001', $data['serial_number']);
        $this->assertNotEmpty($data['test']);
        $this->assertNotEmpty($data['warranty']);
    }

    public function test_solar_erp_module_renders_projects_and_surveys(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/solar');
        $res->assertStatus(200);
        $res->assertSee('Solar ERP', false);
        $res->assertSee('EPC Project Suite', false);
        $res->assertSee('SP-DEL-2026-01', false);
    }

    public function test_manufacturing_module_shows_bom_and_orders(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/manufacturing');
        $res->assertStatus(200);
        $res->assertSee('Manufacturing', false);
        $res->assertSee('Production Management', false);
        $res->assertSee('BOM-FZ-512100', false);
    }

    public function test_payroll_and_payslip_view(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/payroll');
        $res->assertStatus(200);
        $res->assertSee('Payroll', false);
        $res->assertSee('Human Resources', false);

        $payslipRes = $this->get('/payroll/payslips/1');
        $payslipRes->assertStatus(200);
        $payslipRes->assertSee('Salary Payslip', false);
    }

    public function test_dealer_management_module(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/dealers');
        $res->assertStatus(200);
        $res->assertSee('Dealer', false);
        $res->assertSee('Channel Partner', false);
        $res->assertSee('DLR-DEL-01', false);
        $res->assertSee('Surya Shakti Solar', false);
    }

    public function test_service_tickets_module(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/service');
        $res->assertStatus(200);
        $res->assertSee('Service', false);
        $res->assertSee('Warranty Support', false);
        $res->assertSee('SRV-2026-001', false);
    }

    public function test_crm_sales_pipeline(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/crm');
        $res->assertStatus(200);
        $res->assertSee('Sales Opportunity Pipeline', false);
    }

    public function test_fixed_assets_and_expenses(): void
    {
        $this->actingAs($this->getAdmin());

        $res = $this->get('/assets-expenses');
        $res->assertStatus(200);
        $res->assertSee('Fixed Assets', false);
        $res->assertSee('Operating Expenses', false);
    }

    public function test_ai_business_assistant_answers_queries_in_english_and_hindi(): void
    {
        $this->actingAs($this->getAdmin());

        // Test English query
        $resEn = $this->postJson('/ai-assistant/ask', [
            'query' => 'What is today sales?'
        ]);
        $resEn->assertStatus(200);
        $resEn->assertJsonStructure(['reply', 'type']);

        // Test Hindi query from Master Prompt
        $resHi = $this->postJson('/ai-assistant/ask', [
            'query' => 'आज की बिक्री कितनी है?'
        ]);
        $resHi->assertStatus(200);
        $resHi->assertJsonStructure(['reply', 'type']);
    }
}
