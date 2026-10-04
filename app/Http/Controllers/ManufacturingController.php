<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Bom;
use App\Models\BomItem;
use App\Models\JobWorker;
use App\Models\JobWorkOrder;
use App\Models\Product;
use App\Models\ProductionMaterial;
use App\Models\ProductionOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManufacturingController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $boms = Bom::with(['product', 'items.rawMaterial'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $productionOrders = ProductionOrder::with(['bom.product', 'warehouse'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $jobWorkers = JobWorker::where('company_id', $company->id)->get();
        $jobWorkOrders = JobWorkOrder::with('jobWorker')
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $products = Product::where('company_id', $company->id)->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();

        return view('manufacturing.index', compact('company', 'boms', 'productionOrders', 'jobWorkers', 'jobWorkOrders', 'products', 'warehouses'));
    }

    public function storeBom(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'bom_code' => 'required|string|unique:boms,bom_code',
            'bom_name' => 'required|string|max:150',
            'output_qty' => 'required|numeric|min:0.01',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'materials' => 'required|array|min:1',
            'materials.*.product_id' => 'required|exists:products,id',
            'materials.*.quantity' => 'required|numeric|min:0.01',
            'materials.*.unit_cost' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($company, $validated) {
            $totalMatCost = 0.0;
            foreach ($validated['materials'] as $mat) {
                $totalMatCost += ((float) $mat['quantity'] * (float) $mat['unit_cost']);
            }

            $labor = (float) ($validated['labor_cost'] ?? 0);
            $overhead = (float) ($validated['overhead_cost'] ?? 0);
            $totalUnitCost = ($totalMatCost + $labor + $overhead) / (float) $validated['output_qty'];

            $bom = Bom::create([
                'company_id' => $company->id,
                'product_id' => $validated['product_id'],
                'bom_code' => $validated['bom_code'],
                'bom_name' => $validated['bom_name'],
                'output_qty' => $validated['output_qty'],
                'labor_cost' => $labor,
                'overhead_cost' => $overhead,
                'total_material_cost' => $totalMatCost,
                'total_unit_cost' => $totalUnitCost,
                'is_active' => true,
            ]);

            foreach ($validated['materials'] as $mat) {
                BomItem::create([
                    'bom_id' => $bom->id,
                    'raw_material_id' => $mat['product_id'],
                    'quantity' => $mat['quantity'],
                    'unit_cost' => $mat['unit_cost'],
                    'total_cost' => (float) $mat['quantity'] * (float) $mat['unit_cost'],
                    'wastage_percent' => 0.0,
                ]);
            }

            return redirect()->route('manufacturing.index')->with('success', "Bill of Materials '{$bom->bom_name}' created successfully.");
        });
    }

    public function storeProductionOrder(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'bom_id' => 'required|exists:boms,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_no' => 'required|string|unique:production_orders,order_no',
            'order_date' => 'required|date',
            'planned_qty' => 'required|numeric|min:1',
            'remarks' => 'nullable|string',
        ]);

        $bom = Bom::with('items')->findOrFail($validated['bom_id']);
        $totalCost = (float) $bom->total_unit_cost * (float) $validated['planned_qty'];

        $order = ProductionOrder::create([
            'company_id' => $company->id,
            'bom_id' => $bom->id,
            'warehouse_id' => $validated['warehouse_id'],
            'order_no' => $validated['order_no'],
            'order_date' => $validated['order_date'],
            'planned_qty' => $validated['planned_qty'],
            'completed_qty' => 0,
            'scrap_qty' => 0,
            'total_cost' => $totalCost,
            'cost_per_unit' => $bom->total_unit_cost,
            'status' => 'planned',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        foreach ($bom->items as $item) {
            ProductionMaterial::create([
                'production_order_id' => $order->id,
                'raw_material_id' => $item->raw_material_id,
                'warehouse_id' => $validated['warehouse_id'],
                'required_qty' => (float) $item->quantity * (float) $validated['planned_qty'],
                'issued_qty' => 0,
                'unit_cost' => $item->unit_cost,
                'total_cost' => (float) $item->quantity * (float) $validated['planned_qty'] * (float) $item->unit_cost,
            ]);
        }

        return redirect()->route('manufacturing.index')->with('success', "Production Order {$order->order_no} created successfully.");
    }

    public function completeProductionOrder(ProductionOrder $order)
    {
        return DB::transaction(function () use ($order) {
            $order->load(['bom.product', 'bom.items.rawMaterial', 'materials']);

            // 1. Deduct raw materials stock
            foreach ($order->materials as $mat) {
                $rawMat = Product::findOrFail($mat->raw_material_id);
                $qty = (float) $mat->required_qty;
                $rawMat->decrement('current_stock', $qty);

                StockMovement::create([
                    'company_id' => $order->company_id,
                    'product_id' => $rawMat->id,
                    'warehouse_id' => $order->warehouse_id,
                    'movement_type' => 'outward',
                    'quantity' => $qty,
                    'rate' => $mat->unit_cost,
                    'total_amount' => $qty * (float) $mat->unit_cost,
                    'movement_date' => now()->toDateString(),
                    'narration' => "Consumed in Production Order {$order->order_no}",
                ]);

                $mat->update(['issued_qty' => $qty]);
            }

            // 2. Increase finished goods stock
            $finishedGood = $order->bom->product;
            $producedQty = (float) $order->planned_qty;
            $finishedGood->increment('current_stock', $producedQty);

            StockMovement::create([
                'company_id' => $order->company_id,
                'product_id' => $finishedGood->id,
                'warehouse_id' => $order->warehouse_id,
                'movement_type' => 'inward',
                'quantity' => $producedQty,
                'rate' => $order->cost_per_unit,
                'total_amount' => $producedQty * (float) $order->cost_per_unit,
                'movement_date' => now()->toDateString(),
                'narration' => "Produced from BOM {$order->bom->bom_code} via Order {$order->order_no}",
            ]);

            $order->update([
                'completed_qty' => $producedQty,
                'status' => 'completed',
                'completion_date' => now(),
            ]);

            return redirect()->route('manufacturing.index')->with('success', "Production Order {$order->order_no} executed! Stock updated: +{$producedQty} {$finishedGood->name}, and all raw material components deducted.");
        });
    }
}
