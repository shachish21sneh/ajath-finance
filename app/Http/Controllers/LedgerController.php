<?php

namespace App\Http\Controllers;

use App\Enums\PartyType;
use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use App\Models\TaxMaster;
use App\Services\AccountingService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    public function __construct(
        protected AccountingService $accountingService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $query = Ledger::with(['group', 'taxMaster'])->where('company_id', $company->id);

        if ($request->has('party_type') && $request->party_type !== 'all') {
            $query->where('party_type', $request->party_type);
        }

        $ledgers = $query->orderBy('name')->get();
        $groups = LedgerGroup::orderBy('name')->get();

        return view('masters.ledgers.index', compact('ledgers', 'groups', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $groups = LedgerGroup::orderBy('nature')->orderBy('name')->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->get();
        return view('masters.ledgers.create', compact('groups', 'taxes', 'company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $data = $request->validate([
            'ledger_group_id' => ['required', 'exists:ledger_groups,id'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['required', 'numeric'],
            'opening_balance_type' => ['required', 'in:Dr,Cr'],
            'party_type' => ['required', 'in:none,customer,supplier,bank,cash,employee'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['nullable', 'string', 'max:5'],
            'credit_limit' => ['nullable', 'numeric'],
            'credit_days' => ['nullable', 'integer'],
        ]);

        $data['company_id'] = $company->id;
        $opening = (float) $data['opening_balance'];
        $data['current_balance'] = $data['opening_balance_type'] === 'Cr' ? -$opening : $opening;

        $ledger = Ledger::create($data);

        ActivityLog::log('Create Ledger', 'Masters', "Created account ledger: {$ledger->name}");

        return redirect()->route('ledgers.index')->with('success', "Ledger '{$ledger->name}' created successfully.");
    }

    public function edit(Ledger $ledger): View
    {
        $company = AccountingHelper::getActiveCompany();
        $groups = LedgerGroup::orderBy('nature')->orderBy('name')->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->get();
        return view('masters.ledgers.edit', compact('ledger', 'groups', 'taxes', 'company'));
    }

    public function update(Request $request, Ledger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'ledger_group_id' => ['required', 'exists:ledger_groups,id'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['required', 'numeric'],
            'opening_balance_type' => ['required', 'in:Dr,Cr'],
            'party_type' => ['required', 'in:none,customer,supplier,bank,cash,employee'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['nullable', 'string', 'max:5'],
            'credit_limit' => ['nullable', 'numeric'],
            'credit_days' => ['nullable', 'integer'],
        ]);

        $ledger->update($data);
        $this->accountingService->recalculateLedgerBalance($ledger);

        ActivityLog::log('Update Ledger', 'Masters', "Updated ledger: {$ledger->name}");

        return redirect()->route('ledgers.index')->with('success', "Ledger '{$ledger->name}' updated successfully.");
    }

    public function statement(Request $request, Ledger $ledger): View
    {
        $fy = AccountingHelper::getActiveFinancialYear();
        $fromDate = $request->get('from_date', $fy ? $fy->start_date->format('Y-m-d') : date('Y-04-01'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));

        $statement = $this->reportService->getLedgerStatement($ledger, $fromDate, $toDate);

        return view('reports.ledger-statement', compact('ledger', 'statement', 'fromDate', 'toDate'));
    }
}
