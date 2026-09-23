<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::with('financialYears')->orderBy('name')->get();
        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['required', 'string', 'max:5'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code' => ['required', 'string', 'max:10'],
        ]);

        $company = Company::create($data);

        // Auto-create initial Financial Year
        $currentYear = (int) date('Y');
        $startYear = date('m') < 4 ? $currentYear - 1 : $currentYear;
        $endYear = $startYear + 1;

        FinancialYear::create([
            'company_id' => $company->id,
            'title' => "FY {$startYear}-{$endYear}",
            'start_date' => "{$startYear}-04-01",
            'end_date' => "{$endYear}-03-31",
            'is_active' => true,
        ]);

        // Switch to newly created company
        session(['active_company_id' => $company->id]);

        ActivityLog::log('Create Company', 'Company Management', "Created new company: {$company->name}");

        return redirect()->route('companies.index')->with('success', "Company '{$company->name}' created successfully with active financial year.");
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['required', 'string', 'max:5'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code' => ['required', 'string', 'max:10'],
        ]);

        $company->update($data);

        ActivityLog::log('Update Company', 'Company Management', "Updated company profile: {$company->name}");

        return redirect()->route('companies.index')->with('success', "Company profile updated successfully.");
    }

    public function switch(int $id): RedirectResponse
    {
        $company = Company::findOrFail($id);
        session(['active_company_id' => $company->id]);

        // Select active FY for this company
        $activeFy = $company->financialYears()->where('is_active', true)->first();
        if ($activeFy) {
            session(['active_financial_year_id' => $activeFy->id]);
        }

        ActivityLog::log('Switch Company', 'Session Context', "Switched active company context to: {$company->name}");

        return back()->with('success', "Active company switched to '{$company->name}'.");
    }
}
