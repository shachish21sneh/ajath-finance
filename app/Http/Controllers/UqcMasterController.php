<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\UqcMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UqcMasterController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();

        $query = UqcMaster::withCount('units')
            ->where(function ($q) use ($company) {
                $q->whereNull('company_id')->orWhere('company_id', $company->id);
            });

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $uqcs = $query->orderBy('code')->paginate(25)->withQueryString();

        return view('masters.uqc.index', compact('uqcs', 'company'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['name'] = trim($validated['name']);
        $validated['company_id'] = $company->id;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $uqc = UqcMaster::create($validated);

        ActivityLog::log('Create UQC Code', 'Inventory Masters', "Created UQC code: {$uqc->code} - {$uqc->name}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'UQC Code created successfully!',
                'data' => [
                    'id' => $uqc->id,
                    'code' => $uqc->code,
                    'name' => $uqc->name,
                    'display' => "{$uqc->code} - {$uqc->name}",
                ],
            ]);
        }

        return redirect()->route('uqc.index')->with('success', "UQC Code '{$uqc->code} - {$uqc->name}' created successfully.");
    }

    public function update(Request $request, UqcMaster $uqc): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($uqc->company_id && $uqc->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['name'] = trim($validated['name']);
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $uqc->update($validated);

        ActivityLog::log('Update UQC Code', 'Inventory Masters', "Updated UQC code: {$uqc->code} - {$uqc->name}");

        return redirect()->route('uqc.index')->with('success', "UQC Code '{$uqc->code} - {$uqc->name}' updated successfully.");
    }

    public function destroy(UqcMaster $uqc): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if ($uqc->company_id && $uqc->company_id !== $company->id) {
            abort(403);
        }

        if ($uqc->units()->count() > 0) {
            return back()->with('error', "Cannot delete UQC '{$uqc->code}' because it is assigned to {$uqc->units()->count()} measurement unit(s).");
        }

        $code = $uqc->code;
        $name = $uqc->name;
        $uqc->delete();

        ActivityLog::log('Delete UQC Code', 'Inventory Masters', "Deleted UQC code: {$code} - {$name}");

        return redirect()->route('uqc.index')->with('success', "UQC Code '{$code} - {$name}' deleted successfully.");
    }
}
