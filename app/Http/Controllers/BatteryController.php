<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\BatteryModel;
use App\Models\BatterySerial;
use App\Models\BatteryTest;
use App\Models\BatteryWarranty;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarrantyClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatteryController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $models = BatteryModel::with('product')
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $serials = BatterySerial::with(['model.product', 'warehouse', 'customer', 'warranty'])
            ->where('company_id', $company->id)
            ->latest()
            ->take(50)
            ->get();

        $tests = BatteryTest::with('batterySerial.model.product')
            ->latest()
            ->take(30)
            ->get();

        $warranties = BatteryWarranty::with(['batterySerial.model.product', 'customer', 'claims'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $claims = WarrantyClaim::with(['warranty.batterySerial.model.product', 'warranty.customer'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $products = Product::where('company_id', $company->id)->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();
        $customers = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->get();

        $searchQuery = $request->query('serial');
        $searchedSerial = null;
        if ($searchQuery) {
            $searchedSerial = BatterySerial::with(['model.product', 'warehouse', 'customer', 'tests', 'warranty.claims'])
                ->where('company_id', $company->id)
                ->where('serial_number', 'like', "%{$searchQuery}%")
                ->first();
        }

        return view('battery.index', compact('company', 'models', 'serials', 'tests', 'warranties', 'claims', 'products', 'warehouses', 'customers', 'searchedSerial', 'searchQuery'));
    }

    public function storeModel(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:battery_models,product_id',
            'chemistry' => 'required|in:Lead Acid,Tubular,Lithium Ion,LiFePO4,E-Rickshaw,EV',
            'nominal_voltage' => 'required|numeric|min:1',
            'capacity_ah' => 'required|numeric|min:1',
            'bms_model' => 'nullable|string',
            'warranty_months' => 'required|integer|min:6',
            'free_replacement_months' => 'required|integer|min:0',
            'cell_type' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['energy_wh'] = (float) $validated['nominal_voltage'] * (float) $validated['capacity_ah'];
        $validated['pro_rata_months'] = max(0, (int) $validated['warranty_months'] - (int) $validated['free_replacement_months']);

        BatteryModel::create($validated);

        return redirect()->route('battery.index')->with('success', 'Battery Model specification created successfully.');
    }

    public function storeSerial(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'battery_model_id' => 'required|exists:battery_models,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'serial_number' => 'required|string|unique:battery_serials,serial_number',
            'cell_batch_number' => 'nullable|string',
            'mfg_date' => 'required|date',
        ]);

        $validated['company_id'] = $company->id;
        $validated['qc_status'] = 'PASSED';
        $validated['current_status'] = 'IN_STOCK';

        $serial = BatterySerial::create($validated);

        return redirect()->route('battery.index')->with('success', "Battery Serial {$serial->serial_number} registered in inventory.");
    }

    public function storeTest(Request $request)
    {
        $validated = $request->validate([
            'battery_serial_id' => 'required|exists:battery_serials,id',
            'test_date' => 'required|date',
            'open_circuit_voltage' => 'required|numeric',
            'pack_voltage' => 'required|numeric',
            'internal_resistance_mohm' => 'required|numeric',
            'actual_capacity_ah' => 'required|numeric',
            'qc_result' => 'required|in:PASS,FAIL',
            'technician_name' => 'required|string',
            'certificate_no' => 'required|string|unique:battery_tests,certificate_no',
            'remarks' => 'nullable|string',
        ]);

        BatteryTest::create($validated);

        $serial = BatterySerial::find($validated['battery_serial_id']);
        $serial->update(['qc_status' => $validated['qc_result'] === 'PASS' ? 'PASSED' : 'FAILED']);

        return redirect()->route('battery.index')->with('success', "QC Test Certificate {$validated['certificate_no']} recorded for Serial {$serial->serial_number}.");
    }

    public function storeWarranty(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'battery_serial_id' => 'required|exists:battery_serials,id|unique:battery_warranties,battery_serial_id',
            'customer_ledger_id' => 'required|exists:ledgers,id',
            'invoice_no' => 'nullable|string',
            'purchase_date' => 'required|date',
            'warranty_months' => 'required|integer|min:6',
        ]);

        $serial = BatterySerial::findOrFail($validated['battery_serial_id']);
        $startDate = $validated['purchase_date'];
        $endDate = date('Y-m-d', strtotime("{$startDate} +{$validated['warranty_months']} months"));

        BatteryWarranty::create([
            'company_id' => $company->id,
            'battery_serial_id' => $serial->id,
            'customer_ledger_id' => $validated['customer_ledger_id'],
            'invoice_no' => $validated['invoice_no'] ?? null,
            'purchase_date' => $startDate,
            'warranty_start_date' => $startDate,
            'warranty_end_date' => $endDate,
            'warranty_type' => 'Standard Comprehensive',
            'is_active' => true,
        ]);

        $serial->update([
            'current_status' => 'INSTALLED',
            'customer_ledger_id' => $validated['customer_ledger_id'],
            'dispatch_date' => $startDate,
        ]);

        return redirect()->route('battery.index')->with('success', "Warranty successfully registered for Serial {$serial->serial_number} until " . date('d M Y', strtotime($endDate)));
    }

    public function storeClaim(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'battery_warranty_id' => 'required|exists:battery_warranties,id',
            'claim_no' => 'required|string|unique:warranty_claims,claim_no',
            'claim_date' => 'required|date',
            'complaint_description' => 'required|string',
            'action_taken' => 'required|in:PENDING,REPAIR,REPLACEMENT,REJECTED',
            'diagnosis' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = $validated['action_taken'] === 'PENDING' ? 'PENDING' : 'APPROVED';

        WarrantyClaim::create($validated);

        return redirect()->route('battery.index')->with('success', "Warranty Claim {$validated['claim_no']} logged.");
    }

    public function showSerialJson(string $serial)
    {
        $company = AccountingHelper::getActiveCompany();
        $record = BatterySerial::with(['model.product', 'warehouse', 'customer', 'tests', 'warranty.claims'])
            ->where('company_id', $company->id)
            ->where('serial_number', $serial)
            ->firstOrFail();

        return response()->json([
            'serial_number' => $record->serial_number,
            'model' => $record->model?->product?->name,
            'status' => $record->current_status,
            'test' => $record->tests->first(),
            'warranty' => $record->warranty,
        ]);
    }
}
