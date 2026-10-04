<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\SolarAmc;
use App\Models\SolarLead;
use App\Models\SolarProduct;
use App\Models\SolarProject;
use App\Models\SolarQuotation;
use App\Models\SolarSiteSurvey;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolarController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $products = SolarProduct::with('product')
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $leads = SolarLead::where('company_id', $company->id)->latest()->get();
        $surveys = SolarSiteSurvey::with(['lead', 'customer'])->where('company_id', $company->id)->latest()->get();
        $quotations = SolarQuotation::with(['lead', 'customer'])->where('company_id', $company->id)->latest()->get();
        $projects = SolarProject::with(['customer', 'quotation'])->where('company_id', $company->id)->latest()->get();
        $amcs = SolarAmc::with('project.customer')->where('company_id', $company->id)->latest()->get();

        $rawProducts = Product::where('company_id', $company->id)->get();
        $customers = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->get();

        return view('solar.index', compact('company', 'products', 'leads', 'surveys', 'quotations', 'projects', 'amcs', 'rawProducts', 'customers'));
    }

    public function storeProduct(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:solar_products,product_id',
            'product_type' => 'required|in:SOLAR_PANEL,INVERTER,HYBRID_INVERTER,BATTERY,ACDB,DCDB,MC4,CABLE,STRUCTURE',
            'wattage' => 'nullable|numeric|min:0',
            'voltage' => 'nullable|numeric|min:0',
            'efficiency_percent' => 'nullable|numeric|min:0|max:100',
            'warranty_years' => 'required|integer|min:1',
        ]);

        $validated['company_id'] = $company->id;
        SolarProduct::create($validated);

        return redirect()->route('solar.index')->with('success', 'Solar Product specification registered successfully.');
    }

    public function storeLead(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'estimated_kw' => 'required|numeric|min:1',
            'source' => 'nullable|string',
            'status' => 'required|in:NEW,CONTACTED,SURVEY_SCHEDULED,QUOTED,NEGOTIATION,WON,LOST',
        ]);

        $validated['company_id'] = $company->id;
        SolarLead::create($validated);

        return redirect()->route('solar.index')->with('success', 'Solar Inquiry Lead recorded successfully.');
    }

    public function storeSurvey(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'solar_lead_id' => 'nullable|exists:solar_leads,id',
            'customer_ledger_id' => 'nullable|exists:ledgers,id',
            'survey_date' => 'required|date',
            'surveyor_name' => 'required|string|max:100',
            'roof_type' => 'required|in:RCC Flat,Metal Sheet,Sloped Tile,Ground Mounted',
            'roof_area_sqft' => 'required|numeric|min:50',
            'shadow_free_area_sqft' => 'required|numeric|min:50',
            'tilt_angle' => 'required|numeric',
            'sanctioned_load_kw' => 'required|numeric|min:1',
            'monthly_consumption_kwh' => 'required|numeric|min:1',
            'phase' => 'required|in:Single Phase,Three Phase',
            'consumer_number' => 'nullable|string',
            'discom_name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        // Sizing formula: approx 100 sqft shadow free per 1 kW system capacity
        $maxKwByArea = round((float) $validated['shadow_free_area_sqft'] / 90.0, 1);
        $kwByLoad = (float) $validated['sanctioned_load_kw'];
        $validated['recommended_capacity_kw'] = min($maxKwByArea, $kwByLoad);

        SolarSiteSurvey::create($validated);

        return redirect()->route('solar.index')->with('success', "Rooftop Site Survey saved. Recommended system capacity: {$validated['recommended_capacity_kw']} kW.");
    }

    public function storeQuotation(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'solar_lead_id' => 'nullable|exists:solar_leads,id',
            'customer_ledger_id' => 'nullable|exists:ledgers,id',
            'quotation_no' => 'required|string|unique:solar_quotations,quotation_no',
            'quotation_date' => 'required|date',
            'system_capacity_kw' => 'required|numeric|min:1',
            'panel_model' => 'nullable|string',
            'inverter_model' => 'nullable|string',
            'battery_model' => 'nullable|string',
            'system_cost' => 'required|numeric|min:1000',
        ]);

        $cap = (float) $validated['system_capacity_kw'];
        $panelQty = (int) ceil(($cap * 1000) / 550); // using 550W panels
        $cost = (float) $validated['system_cost'];
        $gst = round($cost * 0.138, 2); // 13.8% composite solar GST
        // PM Surya Ghar subsidy rule: ₹30,000/kW up to 2kW, ₹18,000 for 3rd kW (Max ₹78,000)
        $subsidy = min(78000.0, ($cap <= 2 ? $cap * 30000 : 60000 + min(1, $cap - 2) * 18000));
        $netPayable = $cost + $gst - $subsidy;
        $monthlyUnits = round($cap * 120, 0); // ~120 units generated per kW per month
        $paybackYears = round($netPayable / max(1, ($monthlyUnits * 8 * 12)), 1); // at ₹8/kWh grid tariff

        SolarQuotation::create([
            'company_id' => $company->id,
            'solar_lead_id' => $validated['solar_lead_id'] ?? null,
            'customer_ledger_id' => $validated['customer_ledger_id'] ?? null,
            'quotation_no' => $validated['quotation_no'],
            'quotation_date' => $validated['quotation_date'],
            'system_capacity_kw' => $cap,
            'panel_model' => $validated['panel_model'] ?? 'Fuzurra 550W Mono PERC Bifacial',
            'panel_qty' => $panelQty,
            'inverter_model' => $validated['inverter_model'] ?? 'Durasol DSH-3370 Hybrid 5kW',
            'inverter_qty' => 1,
            'battery_model' => $validated['battery_model'] ?? 'Fuzurra 51.2V 100Ah LiFePO4',
            'battery_qty' => $cap >= 5 ? 1 : 0,
            'system_cost' => $cost,
            'gst_amount' => $gst,
            'subsidy_amount' => $subsidy,
            'net_payable' => $netPayable,
            'estimated_monthly_gen_units' => $monthlyUnits,
            'payback_years' => $paybackYears,
            'status' => 'SENT',
        ]);

        return redirect()->route('solar.index')->with('success', "Solar System Quotation {$validated['quotation_no']} created. Subsidy computed: ₹ " . number_format($subsidy, 2) . ", Net Payable: ₹ " . number_format($netPayable, 2));
    }

    public function storeProject(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'customer_ledger_id' => 'required|exists:ledgers,id',
            'quotation_id' => 'nullable|exists:solar_quotations,id',
            'project_code' => 'required|string|unique:solar_projects,project_code',
            'project_name' => 'required|string|max:150',
            'site_address' => 'required|string',
            'capacity_kw' => 'required|numeric|min:1',
            'total_project_cost' => 'required|numeric|min:1000',
            'installation_status' => 'required|in:NOT_STARTED,MATERIAL_DISPATCHED,STRUCTURE_COMPLETED,WIRING_COMPLETED,COMMISSIONED',
            'net_metering_status' => 'required|in:APPLIED,FEASIBILITY_APPROVED,METER_INSTALLED,COMMISSIONED',
            'subsidy_status' => 'required|in:NOT_APPLICABLE,APPLIED,APPROVED,DISBURSED',
            'start_date' => 'nullable|date',
            'commissioning_date' => 'nullable|date',
        ]);

        $validated['company_id'] = $company->id;
        SolarProject::create($validated);

        return redirect()->route('solar.index')->with('success', "Solar Project {$validated['project_code']} registered and assigned to workflow.");
    }
}
