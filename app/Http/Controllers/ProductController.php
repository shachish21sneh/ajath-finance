<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $products = Product::with(['stockGroup', 'unit', 'taxMaster'])
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        return view('masters.products.index', compact('products', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $groups = StockGroup::where('company_id', $company->id)->get();
        $units = Unit::where('company_id', $company->id)->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();

        return view('masters.products.create', compact('groups', 'units', 'taxes', 'warehouses', 'company'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'item_type' => ['required', 'in:goods,service'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'sac_code' => ['nullable', 'string', 'max:20'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'stock_group_id' => ['nullable', 'exists:stock_groups,id'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
            'purchase_price' => ['required', 'numeric'],
            'selling_price' => ['required', 'numeric'],
            'mrp' => ['nullable', 'numeric'],
            'opening_stock' => ['nullable', 'numeric'],
            'reorder_level' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $data['company_id'] = $company->id;
        $data['current_stock'] = (float) ($data['opening_stock'] ?? 0);
        $data['opening_stock_valuation'] = $data['current_stock'] * (float) $data['purchase_price'];

        $product = Product::create($data);

        ActivityLog::log('Create Product', 'Inventory', "Created item: {$product->name}");

        return redirect()->route('products.index')->with('success', "Item '{$product->name}' created successfully.");
    }

    public function edit(Product $product): View
    {
        $company = AccountingHelper::getActiveCompany();
        $groups = StockGroup::where('company_id', $company->id)->get();
        $units = Unit::where('company_id', $company->id)->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->get();

        return view('masters.products.edit', compact('product', 'groups', 'units', 'taxes', 'company'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'item_type' => ['required', 'in:goods,service'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'sac_code' => ['nullable', 'string', 'max:20'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'stock_group_id' => ['nullable', 'exists:stock_groups,id'],
            'tax_master_id' => ['nullable', 'exists:tax_masters,id'],
            'purchase_price' => ['required', 'numeric'],
            'selling_price' => ['required', 'numeric'],
            'mrp' => ['nullable', 'numeric'],
            'reorder_level' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $product->update($data);

        ActivityLog::log('Update Product', 'Inventory', "Updated item: {$product->name}");

        return redirect()->route('products.index')->with('success', "Item '{$product->name}' updated successfully.");
    }
}
