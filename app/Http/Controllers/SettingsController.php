<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $phpVersion = PHP_VERSION;
        $dbConnection = config('database.default');
        $sessionDriver = config('session.driver');
        $cacheDriver = config('cache.default');

        $stats = [
            'total_ledgers' => Ledger::where('company_id', $company?->id)->count(),
            'total_products' => Product::where('company_id', $company?->id)->count(),
            'total_vouchers' => Voucher::where('company_id', $company?->id)->count(),
            'total_invoices' => SalesInvoice::where('company_id', $company?->id)->count(),
        ];

        return view('settings.index', compact('company', 'phpVersion', 'dbConnection', 'sessionDriver', 'cacheDriver', 'stats'));
    }

    public function backup(): StreamedResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $date = date('Y-m-d_H-i-s');
        $filename = "ajath_erp_backup_{$company->id}_{$date}.json";

        ActivityLog::log('Backup Export', 'Maintenance', 'Downloaded company database export.');

        $exportData = [
            'company' => $company,
            'financial_years' => FinancialYear::where('company_id', $company->id)->get(),
            'ledgers' => Ledger::where('company_id', $company->id)->get(),
            'products' => Product::where('company_id', $company->id)->get(),
            'vouchers' => Voucher::with('items')->where('company_id', $company->id)->get(),
            'invoices' => SalesInvoice::with('items')->where('company_id', $company->id)->get(),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}
