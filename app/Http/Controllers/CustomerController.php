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

class CustomerController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $customers = Ledger::customers()->where('company_id', $company->id)->orderBy('name')->get();
        return view('masters.customers.index', compact('customers', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('masters.customers.create', compact('company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $debtorsGroup = LedgerGroup::where('slug', 'sundry-debtors')->first() ?? LedgerGroup::where('nature', 'ASSET')->first();

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
        $data['ledger_group_id'] = $debtorsGroup->id;
        $data['party_type'] = PartyType::CUSTOMER;
        $opening = (float) $data['opening_balance'];
        $data['current_balance'] = $data['opening_balance_type'] === 'Cr' ? -$opening : $opening;

        $ledger = Ledger::create($data);

        ActivityLog::log('Create Customer', 'Masters', "Created customer: {$ledger->name}");

        return redirect()->route('customers.index')->with('success', "Customer '{$ledger->name}' registered successfully.");
    }

    public function edit(Ledger $customer): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('masters.customers.edit', compact('customer', 'company'));
    }

    public function update(Request $request, Ledger $customer): RedirectResponse
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

        $customer->update($data);

        ActivityLog::log('Update Customer', 'Masters', "Updated customer details: {$customer->name}");

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' updated successfully.");
    }
}
