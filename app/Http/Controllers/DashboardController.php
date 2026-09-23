<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $metrics = $company ? $this->reportService->getDashboardMetrics($company) : [
            'sales_total' => 0,
            'purchase_total' => 0,
            'receivables' => 0,
            'payables' => 0,
            'cash_balance' => 0,
            'bank_balance' => 0,
            'recent_vouchers' => collect(),
            'top_products' => collect(),
        ];

        return view('dashboard.index', compact('company', 'metrics'));
    }
}
