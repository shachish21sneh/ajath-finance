<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FinancialReportsTest extends TestCase
{
    public function test_financial_reports_render_successfully(): void
    {
        $admin = User::where('email', 'admin@ajath.com')->first();
        $this->actingAs($admin);

        // Day Book
        $r1 = $this->get('/reports/day-book');
        $r1->assertStatus(200);
        $r1->assertSee('Day Book');

        // Trial Balance
        $r2 = $this->get('/reports/trial-balance');
        $r2->assertStatus(200);
        $r2->assertSee('Trial Balance');

        // Profit & Loss
        $r3 = $this->get('/reports/profit-loss');
        $r3->assertStatus(200);
        $r3->assertSee('Profit & Loss');

        // Balance Sheet
        $r4 = $this->get('/reports/balance-sheet');
        $r4->assertStatus(200);
        $r4->assertSee('Balance Sheet');

        // GST Report
        $r5 = $this->get('/reports/gst');
        $r5->assertStatus(200);
        $r5->assertSee('GSTR-1');
    }
}
