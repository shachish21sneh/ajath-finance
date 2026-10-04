<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\TaxMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxMasterController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $taxes = TaxMaster::withCount(['products', 'ledgers'])
            ->where('company_id', $company->id)
            ->orderBy('rate')
            ->paginate(20);

        return view('masters.taxes.index', compact('taxes', 'company'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'cgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'igst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cess_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rate = (float) $validated['rate'];
        $validated['company_id'] = $company->id;
        $validated['cgst_rate'] = $validated['cgst_rate'] ?? ($rate / 2);
        $validated['sgst_rate'] = $validated['sgst_rate'] ?? ($rate / 2);
        $validated['igst_rate'] = $validated['igst_rate'] ?? $rate;
        $validated['cess_rate'] = $validated['cess_rate'] ?? 0.00;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $tax = TaxMaster::create($validated);

        ActivityLog::log('Create Tax Rate', 'Accounting Masters', "Created GST Tax Rate: {$tax->name} ({$tax->rate}%)");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'GST Tax Rate created successfully!',
                'data' => [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => (float)$tax->rate,
                    'display' => "{$tax->name} ({$tax->rate}%)",
                ],
            ]);
        }

        return redirect()->route('taxes.index')->with('success', "GST Tax Rate '{$tax->name}' created successfully.");
    }

    public function update(Request $request, TaxMaster $tax): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($tax->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'cgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'igst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cess_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rate = (float) $validated['rate'];
        $validated['cgst_rate'] = $validated['cgst_rate'] ?? ($rate / 2);
        $validated['sgst_rate'] = $validated['sgst_rate'] ?? ($rate / 2);
        $validated['igst_rate'] = $validated['igst_rate'] ?? $rate;
        $validated['cess_rate'] = $validated['cess_rate'] ?? 0.00;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $tax->update($validated);

        ActivityLog::log('Update Tax Rate', 'Accounting Masters', "Updated GST Tax Rate: {$tax->name} ({$tax->rate}%)");

        return redirect()->route('taxes.index')->with('success', "GST Tax Rate '{$tax->name}' updated successfully.");
    }

    public function destroy(TaxMaster $tax): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($tax->company_id !== $company->id) {
            abort(403);
        }

        $prodCount = $tax->products()->count();
        $ledgerCount = $tax->ledgers()->count();
        if ($prodCount > 0 || $ledgerCount > 0) {
            return back()->with('error', "Cannot delete '{$tax->name}' because it is assigned to {$prodCount} product(s) and {$ledgerCount} ledger(s).");
        }

        $name = $tax->name;
        $tax->delete();

        ActivityLog::log('Delete Tax Rate', 'Accounting Masters', "Deleted GST Tax Rate: {$name}");

        return redirect()->route('taxes.index')->with('success', "GST Tax Rate '{$name}' deleted successfully.");
    }
}
