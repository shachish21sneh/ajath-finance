<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $warehouses = Warehouse::where('company_id', $company->id)->paginate(15);
        return view('masters.warehouses.index', compact('warehouses', 'company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $data['company_id'] = $company->id;
        Warehouse::create($data);

        ActivityLog::log('Create Warehouse', 'Inventory', "Created godown/depot: {$data['name']}");

        return back()->with('success', "Godown '{$data['name']}' added successfully.");
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = $request->has('is_default');
        if ($data['is_default']) {
            Warehouse::where('company_id', $warehouse->company_id)->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update($data);

        ActivityLog::log('Update Warehouse', 'Inventory', "Updated godown/depot: {$warehouse->name}");

        return back()->with('success', "Godown '{$warehouse->name}' updated successfully.");
    }
}
