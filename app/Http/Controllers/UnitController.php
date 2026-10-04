<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $units = Unit::withCount('products')
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->paginate(20);

        return view('masters.units.index', compact('units', 'company'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:20'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $validated['company_id'] = $company->id;
        $validated['decimal_places'] = $validated['decimal_places'] ?? 0;

        $unit = Unit::create($validated);

        ActivityLog::log('Create Unit', 'Inventory Masters', "Created measurement unit: {$unit->name} ({$unit->symbol})");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Measurement Unit created successfully!',
                'data' => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'symbol' => $unit->symbol,
                    'display' => "{$unit->name} ({$unit->symbol})",
                ],
            ]);
        }

        return redirect()->route('units.index')->with('success', "Measurement Unit '{$unit->name} ({$unit->symbol})' created successfully.");
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($unit->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:20'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
        ]);

        $validated['decimal_places'] = $validated['decimal_places'] ?? 0;

        $unit->update($validated);

        ActivityLog::log('Update Unit', 'Inventory Masters', "Updated measurement unit: {$unit->name} ({$unit->symbol})");

        return redirect()->route('units.index')->with('success', "Measurement Unit '{$unit->name} ({$unit->symbol})' updated successfully.");
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($unit->company_id !== $company->id) {
            abort(403);
        }

        if ($unit->products()->count() > 0) {
            return back()->with('error', "Cannot delete '{$unit->name}' because {$unit->products()->count()} product(s) are assigned to it.");
        }

        $name = $unit->name;
        $symbol = $unit->symbol;
        $unit->delete();

        ActivityLog::log('Delete Unit', 'Inventory Masters', "Deleted measurement unit: {$name} ({$symbol})");

        return redirect()->route('units.index')->with('success', "Measurement Unit '{$name} ({$symbol})' deleted successfully.");
    }
}
