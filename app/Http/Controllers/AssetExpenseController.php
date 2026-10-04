<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\CostCentre;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRecord;
use App\Models\FixedAsset;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $assets = FixedAsset::with('ledger')->where('company_id', $company->id)->latest()->get();
        $categories = ExpenseCategory::with('ledger')->where('company_id', $company->id)->get();
        $expenses = ExpenseRecord::with(['category', 'costCentre'])->where('company_id', $company->id)->latest('expense_date')->get();
        $costCentres = CostCentre::where('company_id', $company->id)->get();
        $ledgers = Ledger::where('company_id', $company->id)->get();

        $totalAssetCost = (float) $assets->sum('purchase_cost');
        $totalBookValue = (float) $assets->sum('book_value');
        $totalExpenses = (float) $expenses->sum('amount');

        return view('assets-expenses.index', compact('company', 'assets', 'categories', 'expenses', 'costCentres', 'ledgers', 'totalAssetCost', 'totalBookValue', 'totalExpenses'));
    }

    public function storeAsset(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'asset_name' => 'required|string|max:150',
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:SLM,WDV',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
            'location' => 'nullable|string',
            'department' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['accumulated_depreciation'] = 0.00;
        $validated['book_value'] = $validated['purchase_cost'];
        $validated['status'] = 'ACTIVE';

        FixedAsset::create($validated);

        return redirect()->route('assets-expenses.index')->with('success', "Fixed Asset '{$validated['asset_name']}' logged in Asset Register.");
    }

    public function storeExpense(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'cost_centre_id' => 'nullable|exists:cost_centres,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'paid_to' => 'required|string|max:150',
            'payment_mode' => 'required|in:CASH,BANK_TRANSFER,CHEQUE,UPI,CREDIT_CARD',
            'reference_no' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = 'APPROVED';

        ExpenseRecord::create($validated);

        return redirect()->route('assets-expenses.index')->with('success', "Operating Expense of ₹ " . number_format($validated['amount'], 2) . " posted.");
    }

    public function storeCostCentre(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string',
            'type' => 'required|in:BRANCH,DEPARTMENT,PROJECT,DIVISION',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        CostCentre::create($validated);

        return redirect()->route('assets-expenses.index')->with('success', "Cost Centre '{$validated['name']}' created.");
    }
}
