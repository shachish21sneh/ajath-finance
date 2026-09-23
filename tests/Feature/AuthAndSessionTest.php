<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class AuthAndSessionTest extends TestCase
{
    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Ajath Cloud ERP');
    }

    public function test_user_can_authenticate_and_access_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@ajath.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Financial Overview & Dashboard', false);
    }

    public function test_switching_company_changes_session_context(): void
    {
        $admin = User::where('email', 'admin@ajath.com')->first();
        $this->actingAs($admin);

        $newCompany = Company::create([
            'name' => 'Secondary Enterprise Ltd',
            'state' => 'Delhi',
            'state_code' => '07',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
        ]);

        $response = $this->post("/companies/{$newCompany->id}/switch");
        $response->assertSessionHas('active_company_id', $newCompany->id);
    }
}
