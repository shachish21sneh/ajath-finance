<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class PaginationTest extends TestCase
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

    public function test_all_master_and_transaction_lists_render_bootstrap_pagination(): void
    {
        $routes = [
            'sales.index',
            'purchases.index',
            'vouchers.index',
            'customers.index',
            'suppliers.index',
            'products.index',
            'ledgers.index',
            'users.index',
            'companies.index',
            'warehouses.index',
            'stock-groups.index',
            'units.index',
            'taxes.index',
            'uqc.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertStatus(200);

            // Verify view renders pagination component without Tailwind SVG bloat
            $content = $response->getContent();
            $this->assertStringNotContainsString('w-5 h-5', $content, "Route {$routeName} should not contain raw unstyled Tailwind pagination SVG classes.");
        }
    }
}
