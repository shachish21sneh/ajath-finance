<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockGroupController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $stockGroups = StockGroup::with(['parent', 'taxMaster'])
            ->withCount('products')
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->paginate(20);

        $parentGroups = StockGroup::where('company_id', $company->id)->orderBy('name')->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->orderBy('rate')->get();

        return view('masters.stock-groups.index', compact('stockGroups', 'parentGroups', 'taxes', 'company'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'exists:stock_groups,id'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
        ]);

        $validated['company_id'] = $company->id;

        $stockGroup = StockGroup::create($validated);

        ActivityLog::log('Create Stock Group', 'Inventory Masters', "Created stock group: {$stockGroup->name}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock Group created successfully!',
                'data' => [
                    'id' => $stockGroup->id,
                    'name' => $stockGroup->name,
                    'hsn_code' => $stockGroup->hsn_code,
                    'tax_master_id' => $stockGroup->tax_master_id,
                ],
            ]);
        }

        return redirect()->route('stock-groups.index')->with('success', "Stock Group '{$stockGroup->name}' created successfully.");
    }

    public function update(Request $request, StockGroup $stockGroup): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($stockGroup->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'exists:stock_groups,id'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
        ]);

        // Prevent setting itself as parent
        if (!empty($validated['parent_id']) && (int)$validated['parent_id'] === $stockGroup->id) {
            return back()->with('error', 'A stock group cannot be its own parent.');
        }

        $stockGroup->update($validated);

        ActivityLog::log('Update Stock Group', 'Inventory Masters', "Updated stock group: {$stockGroup->name}");

        return redirect()->route('stock-groups.index')->with('success', "Stock Group '{$stockGroup->name}' updated successfully.");
    }

    public function destroy(StockGroup $stockGroup): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($stockGroup->company_id !== $company->id) {
            abort(403);
        }

        if ($stockGroup->products()->count() > 0) {
            return back()->with('error', "Cannot delete '{$stockGroup->name}' because {$stockGroup->products()->count()} product(s) are assigned to it.");
        }

        $name = $stockGroup->name;
        $stockGroup->delete();

        ActivityLog::log('Delete Stock Group', 'Inventory Masters', "Deleted stock group: {$name}");

        return redirect()->route('stock-groups.index')->with('success', "Stock Group '{$name}' deleted successfully.");
    }
}
