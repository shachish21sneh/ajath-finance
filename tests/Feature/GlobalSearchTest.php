<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    public function test_spotlight_search_api_returns_categorized_results(): void
    {
        $admin = User::where('email', 'admin@fuzurra.com')->first();
        $this->actingAs($admin);

        $response = $this->getJson('/api/search?q=Battery');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }
}
