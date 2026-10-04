<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockGroup;
use App\Models\TaxMaster;
use App\Models\Unit;
use App\Models\UqcMaster;
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
        $products = Product::with(['stockGroup', 'unit', 'taxMaster', 'inventoryComponents.componentProduct.unit'])
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
        $uqcs = UqcMaster::where(fn($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderBy('code')->get();
        $availableProducts = Product::with(['taxMaster', 'unit'])
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('masters.products.create', compact('groups', 'units', 'taxes', 'warehouses', 'uqcs', 'availableProducts', 'company'));
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
            'has_inventory_components' => ['nullable', 'boolean'],
            'components' => ['nullable', 'array'],
        ]);

        $data['company_id'] = $company->id;
        $data['current_stock'] = (float) ($data['opening_stock'] ?? 0);
        $data['opening_stock_valuation'] = $data['current_stock'] * (float) $data['purchase_price'];
        $data['has_inventory_components'] = $request->boolean('has_inventory_components');

        $product = Product::create($data);

        // Save inventory components if enabled
        if ($product->has_inventory_components && $request->has('components')) {
            foreach ($request->input('components') as $comp) {
                if (!empty($comp['name'])) {
                    $product->inventoryComponents()->create([
                        'company_id' => $company->id,
                        'component_product_id' => !empty($comp['component_product_id']) ? $comp['component_product_id'] : null,
                        'name' => $comp['name'],
                        'hsn_code' => $comp['hsn_code'] ?? null,
                        'gst_rate' => (float) ($comp['gst_rate'] ?? 0),
                        'unit_price' => (float) ($comp['unit_price'] ?? 0),
                        'quantity' => (float) ($comp['quantity'] ?? 1),
                    ]);
                }
            }
        }

        ActivityLog::log('Create Product', 'Inventory', "Created item: {$product->name}" . ($product->has_inventory_components ? " with linked components" : ""));

        return redirect()->route('products.index')->with('success', "Item '{$product->name}' created successfully.");
    }

    public function edit(Product $product): View
    {
        $company = AccountingHelper::getActiveCompany();
        $groups = StockGroup::where('company_id', $company->id)->get();
        $units = Unit::where('company_id', $company->id)->get();
        $taxes = TaxMaster::where('company_id', $company->id)->where('is_active', true)->get();
        $uqcs = UqcMaster::where(fn($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderBy('code')->get();
        $availableProducts = Product::with(['taxMaster', 'unit'])
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get();

        $product->load('inventoryComponents.componentProduct.unit');

        return view('masters.products.edit', compact('product', 'groups', 'units', 'taxes', 'uqcs', 'availableProducts', 'company'));
    }

    public function update(Request $request, Product $product): RedirectResponse
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
            'reorder_level' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'has_inventory_components' => ['nullable', 'boolean'],
            'components' => ['nullable', 'array'],
        ]);

        $data['has_inventory_components'] = $request->boolean('has_inventory_components');
        $product->update($data);

        // Sync inventory components
        $product->inventoryComponents()->delete();
        if ($product->has_inventory_components && $request->has('components')) {
            foreach ($request->input('components') as $comp) {
                if (!empty($comp['name'])) {
                    $product->inventoryComponents()->create([
                        'company_id' => $company->id,
                        'component_product_id' => !empty($comp['component_product_id']) ? $comp['component_product_id'] : null,
                        'name' => $comp['name'],
                        'hsn_code' => $comp['hsn_code'] ?? null,
                        'gst_rate' => (float) ($comp['gst_rate'] ?? 0),
                        'unit_price' => (float) ($comp['unit_price'] ?? 0),
                        'quantity' => (float) ($comp['quantity'] ?? 1),
                    ]);
                }
            }
        }

        ActivityLog::log('Update Product', 'Inventory', "Updated item: {$product->name}");

        return redirect()->route('products.index')->with('success', "Item '{$product->name}' updated successfully.");
    }
}
