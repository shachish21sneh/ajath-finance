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
use Illuminate\Http\JsonResponse;
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
            ->paginate(20);

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

    public function searchAjax(Request $request): JsonResponse
    {
        $company = AccountingHelper::getActiveCompany();
        if (!$company) {
            return response()->json([]);
        }

        $query = trim((string) $request->get('q', ''));
        $type = $request->get('type', 'sales'); // 'sales' or 'purchase'
        $limit = (int) $request->get('limit', 25);
        $limit = min(max($limit, 5), 50);

        $productsQuery = Product::with([
            'taxMaster', 
            'unit', 
            'inventoryComponents.componentProduct.unit',
            'salesInvoiceItems' => fn($q) => $q->select('id', 'product_id', 'description')->whereNotNull('description')->where('description', '!=', ''),
            'purchaseInvoiceItems' => fn($q) => $q->select('id', 'product_id', 'description')->whereNotNull('description')->where('description', '!=', ''),
        ])
            ->where('company_id', $company->id)
            ->where('is_active', true);

        if ($query !== '') {
            $words = array_filter(preg_split('/\s+/', trim($query)));
            $productsQuery->where(function ($b) use ($query, $words) {
                // 1. Direct phrase match on product fields or invoice item descriptions
                $b->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('hsn_code', 'like', "%{$query}%")
                  ->orWhere('sac_code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhereHas('salesInvoiceItems', function ($si) use ($query) {
                      $si->where('description', 'like', "%{$query}%");
                  })
                  ->orWhereHas('purchaseInvoiceItems', function ($pi) use ($query) {
                      $pi->where('description', 'like', "%{$query}%");
                  });

                // 2. Keyword-based multi-term matching
                if (count($words) > 1) {
                    $b->orWhere(function ($sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where(function ($w) use ($word) {
                                $w->where('name', 'like', "%{$word}%")
                                  ->orWhere('sku', 'like', "%{$word}%")
                                  ->orWhere('barcode', 'like', "%{$word}%")
                                  ->orWhere('hsn_code', 'like', "%{$word}%")
                                  ->orWhere('sac_code', 'like', "%{$word}%")
                                  ->orWhere('description', 'like', "%{$word}%")
                                  ->orWhereHas('salesInvoiceItems', function ($si) use ($word) {
                                      $si->where('description', 'like', "%{$word}%");
                                  })
                                  ->orWhereHas('purchaseInvoiceItems', function ($pi) use ($word) {
                                      $pi->where('description', 'like', "%{$word}%");
                                  });
                            });
                        }
                    });
                }
            });
        }

        $products = $productsQuery->orderBy('name')
            ->limit($limit)
            ->get();

        $items = $products->map(function (Product $p) use ($type, $query) {
            $price = $type === 'purchase' ? (float) $p->purchase_price : (float) $p->selling_price;
            $hsn = $p->hsn_code ?: $p->sac_code ?: '';

            $components = $p->inventoryComponents->map(function ($c) {
                return [
                    'name' => $c->name,
                    'qty' => (float) $c->quantity,
                    'stock' => (float) ($c->componentProduct->current_stock ?? 0),
                    'unit' => $c->componentProduct->unit->symbol ?? 'PCS',
                ];
            })->values();

            // Find best matching description to display to user
            $displayDescription = $p->description ?: '';
            if ($query !== '') {
                $words = array_filter(preg_split('/\s+/', trim($query)));
                if ($p->description && stripos($p->description, $query) !== false) {
                    $displayDescription = $p->description;
                } else {
                    $matchedSii = $p->salesInvoiceItems?->first(function ($sii) use ($query, $words) {
                        if (!$sii->description) return false;
                        if (stripos($sii->description, $query) !== false) return true;
                        foreach ($words as $w) {
                            if (stripos($sii->description, $w) !== false) return true;
                        }
                        return false;
                    });
                    if ($matchedSii) {
                        $displayDescription = $matchedSii->description;
                    } else {
                        $matchedPii = $p->purchaseInvoiceItems?->first(function ($pii) use ($query, $words) {
                            if (!$pii->description) return false;
                            if (stripos($pii->description, $query) !== false) return true;
                            foreach ($words as $w) {
                                if (stripos($pii->description, $w) !== false) return true;
                            }
                            return false;
                        });
                        if ($matchedPii) {
                            $displayDescription = $matchedPii->description;
                        }
                    }
                }
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $displayDescription,
                'sku' => $p->sku ?: '',
                'barcode' => $p->barcode ?: '',
                'hsn' => $hsn,
                'price' => $price,
                'tax_rate' => (float) ($p->taxMaster->rate ?? 0),
                'current_stock' => (float) $p->current_stock,
                'unit_symbol' => $p->unit?->symbol ?: ($p->unit?->name ?: 'PCS'),
                'has_components' => (bool) ($p->has_inventory_components && $components->isNotEmpty()),
                'components_count' => $components->count(),
                'components' => $components,
            ];
        });

        return response()->json($items);
    }
}
