<?php

namespace App\Http\Controllers;

use App\Enums\PartyType;
use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Ledger;
use App\Models\LedgerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $suppliers = Ledger::suppliers()->where('company_id', $company->id)->orderBy('name')->get();
        return view('masters.suppliers.index', compact('suppliers', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('masters.suppliers.create', compact('company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $creditorsGroup = LedgerGroup::where('slug', 'sundry-creditors')->first() ?? LedgerGroup::where('nature', 'LIABILITY')->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'state_code' => ['required', 'string', 'max:5'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'credit_limit' => ['nullable', 'numeric'],
            'credit_days' => ['nullable', 'integer'],
            'opening_balance' => ['required', 'numeric'],
            'opening_balance_type' => ['required', 'in:Dr,Cr'],
        ]);

        $data['company_id'] = $company->id;
        $data['ledger_group_id'] = $creditorsGroup->id;
        $data['party_type'] = PartyType::SUPPLIER;
        $opening = (float) $data['opening_balance'];
        $data['current_balance'] = $data['opening_balance_type'] === 'Cr' ? -$opening : $opening;

        $ledger = Ledger::create($data);

        ActivityLog::log('Create Supplier', 'Masters', "Created supplier: {$ledger->name}");

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$ledger->name}' registered successfully.");
    }

    public function edit(Ledger $supplier): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('masters.suppliers.edit', compact('supplier', 'company'));
    }

    public function update(Request $request, Ledger $supplier): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'state_code' => ['required', 'string', 'max:5'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'credit_limit' => ['nullable', 'numeric'],
            'credit_days' => ['nullable', 'integer'],
        ]);

        $supplier->update($data);

        ActivityLog::log('Update Supplier', 'Masters', "Updated supplier: {$supplier->name}");

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }
}
