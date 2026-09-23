<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Warehouse;
use App\Services\InvoicingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    public function __construct(protected InvoicingService $invoicingService) {}

    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $invoices = PurchaseInvoice::with('supplier')
            ->where('company_id', $company->id)
            ->latest('bill_date')
            ->latest('id')
            ->paginate(20);

        return view('purchases.index', compact('invoices', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $suppliers = Ledger::suppliers()->where('company_id', $company->id)->orderBy('name')->get();
        $products = Product::with('taxMaster')->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();

        return view('purchases.create', compact('suppliers', 'products', 'warehouses', 'company', 'fy'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $data = $request->validate([
            'supplier_ledger_id' => ['required', 'exists:ledgers,id'],
            'bill_no' => ['required', 'string', 'max:50'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string'],
            'items.*.hsn_code' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric'],
            'items.*.gst_rate' => ['required', 'numeric'],
        ]);

        $data['company_id'] = $company->id;
        $data['financial_year_id'] = $fy->id;

        try {
            $invoice = $this->invoicingService->createPurchaseInvoice($data, $data['items']);

            ActivityLog::log(
                'Record Purchase',
                'Purchase',
                "Entered Purchase Bill #{$invoice->bill_no} for amount ₹ " . number_format($invoice->grand_total, 2)
            );

            return redirect()->route('purchases.show', $invoice->id)->with('success', "Purchase Bill #{$invoice->bill_no} recorded and inward stock received.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(PurchaseInvoice $purchase): View
    {
        $purchase->load(['company', 'supplier', 'items.product', 'voucher']);
        return view('purchases.show', compact('purchase'));
    }
}
