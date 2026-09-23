<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Ledger;
use App\Services\GstService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected GstService $gstService
    ) {}

    public function dayBook(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fromDate = $request->get('from_date', date('Y-m-d'));
        $toDate = $request->get('to_date', date('Y-m-d'));
        $voucherType = $request->get('voucher_type');

        $data = $this->reportService->getDayBook($company, $fromDate, $toDate, $voucherType);

        return view('reports.day-book', compact('company', 'fromDate', 'toDate', 'voucherType', 'data'));
    }

    public function trialBalance(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $asOfDate = $request->get('as_of_date', date('Y-m-d'));

        $tb = $this->reportService->getTrialBalance($company, $asOfDate);

        return view('reports.trial-balance', compact('company', 'asOfDate', 'tb'));
    }

    public function profitLoss(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();
        $fromDate = $request->get('from_date', $fy ? $fy->start_date->format('Y-m-d') : date('Y-04-01'));
        $toDate = $request->get('to_date', date('Y-m-d'));

        $pnl = $this->reportService->getProfitAndLoss($company, $fromDate, $toDate);

        return view('reports.profit-loss', compact('company', 'fromDate', 'toDate', 'pnl'));
    }

    public function balanceSheet(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $asOfDate = $request->get('as_of_date', date('Y-m-d'));

        $bs = $this->reportService->getBalanceSheet($company, $asOfDate);

        return view('reports.balance-sheet', compact('company', 'asOfDate', 'bs'));
    }

    public function gst(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();
        $fromDate = $request->get('from_date', $fy ? $fy->start_date->format('Y-m-d') : date('Y-04-01'));
        $toDate = $request->get('to_date', date('Y-m-d'));

        $gstr1 = $this->gstService->getGstr1Summary($company, $fromDate, $toDate);

        return view('reports.gst', compact('company', 'fromDate', 'toDate', 'gstr1'));
    }
}
