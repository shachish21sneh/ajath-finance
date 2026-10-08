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
        $customers = Ledger::customers()->where('company_id', $company->id)->orderBy('name')->paginate(20);
        return view('masters.customers.index', compact('customers', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('masters.customers.create', compact('company'));
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $debtorsGroup = LedgerGroup::where('slug', 'sundry-debtors')->first() ?? LedgerGroup::where('nature', 'ASSET')->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:15'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'state_code' => ['nullable', 'string', 'max:5'],
            'pincode' => ['nullable', 'string', 'max:15'],
            'credit_limit' => ['nullable', 'numeric'],
            'credit_days' => ['nullable', 'integer'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_balance_type' => ['nullable', 'in:Dr,Cr'],
        ]);

        $data['company_id'] = $company->id;
        $data['ledger_group_id'] = $debtorsGroup->id;
        $data['party_type'] = PartyType::CUSTOMER;
        $data['state'] = !empty($data['state']) ? $data['state'] : ($company->state ?? 'Delhi');
        $data['state_code'] = !empty($data['state_code']) ? $data['state_code'] : ($company->state_code ?? '07');
        $opening = isset($data['opening_balance']) ? (float) $data['opening_balance'] : 0.0;
        $data['opening_balance'] = $opening;
        $data['opening_balance_type'] = $data['opening_balance_type'] ?? 'Dr';
        $data['current_balance'] = $data['opening_balance_type'] === 'Cr' ? -$opening : $opening;

        $ledger = Ledger::create($data);

        ActivityLog::log('Create Customer', 'Masters', "Created customer: {$ledger->name}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $ledger->id,
                    'name' => $ledger->name,
                    'state' => $ledger->state,
                    'state_code' => $ledger->state_code,
                    'gstin' => $ledger->gstin,
                    'phone' => $ledger->phone,
                    'city' => $ledger->city,
                    'label' => $ledger->name . ($ledger->state ? ' (' . $ledger->state . ')' : '') . ($ledger->gstin ? ' [GST: ' . $ledger->gstin . ']' : ''),
                ],
                'message' => "Customer '{$ledger->name}' registered successfully."
            ]);
        }

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
