<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Dealer;
use App\Models\DealerCommission;
use App\Models\Ledger;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $dealers = Dealer::with(['ledger', 'commissions'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $commissions = DealerCommission::with(['dealer', 'salesInvoice'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $ledgers = Ledger::where('company_id', $company->id)->get();

        return view('dealers.index', compact('company', 'dealers', 'commissions', 'ledgers'));
    }

    public function store(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'dealer_code' => 'required|string|unique:dealers,dealer_code',
            'name' => 'required|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'territory' => 'nullable|string|max:100',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'price_tier' => 'required|in:DISTRIBUTOR,DEALER,RETAILER',
        ]);

        $validated['company_id'] = $company->id;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 100000;
        $validated['credit_days'] = $validated['credit_days'] ?? 30;

        Dealer::create($validated);

        return redirect()->route('dealers.index')->with('success', "Dealer / Channel Partner '{$validated['name']}' ({$validated['price_tier']}) added successfully.");
    }
}
