<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialYearController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $financialYears = $company ? $company->financialYears()->latest()->get() : collect();
        return view('financial-years.index', compact('company', 'financialYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if (!$company) {
            return back()->with('error', 'Please select an active company first.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $fy = FinancialYear::create([
            'company_id' => $company->id,
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => true,
        ]);

        session(['active_financial_year_id' => $fy->id]);

        ActivityLog::log('Create FY', 'Financial Year', "Created new financial year: {$fy->title}");

        return back()->with('success', "Financial Year '{$fy->title}' created and activated.");
    }

    public function switch(int $id): RedirectResponse
    {
        $fy = FinancialYear::findOrFail($id);
        session(['active_financial_year_id' => $fy->id]);

        ActivityLog::log('Switch FY', 'Financial Year', "Switched active FY to: {$fy->title}");

        return back()->with('success', "Active Financial Year changed to '{$fy->title}'.");
    }
}
