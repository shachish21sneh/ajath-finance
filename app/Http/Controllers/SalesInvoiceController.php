<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Warehouse;
use App\Services\InvoicingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    public function __construct(protected InvoicingService $invoicingService) {}

    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $invoices = SalesInvoice::with('customer')
            ->where('company_id', $company->id)
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(20);

        return view('sales.index', compact('invoices', 'company'));
    }

    public function create(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $invoiceNo = $this->invoicingService->generateInvoiceNumber($company, $fy, 'tax_invoice');
        $customers = Ledger::customers()->where('company_id', $company->id)->orderBy('name')->get();
        $products = Product::with('taxMaster')->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();

        return view('sales.create', compact('invoiceNo', 'customers', 'products', 'warehouses', 'company', 'fy'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $data = $request->validate([
            'customer_ledger_id' => ['required', 'exists:ledgers,id'],
            'invoice_no' => ['nullable', 'string', 'max:50'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'string'],
            'paid_amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
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
        $data['invoice_type'] = 'tax_invoice';

        try {
            $invoice = $this->invoicingService->createSalesInvoice($data, $data['items']);

            ActivityLog::log(
                'Create Invoice',
                'Sales',
                "Generated Tax Invoice #{$invoice->invoice_no} for amount ₹ " . number_format($invoice->grand_total, 2)
            );

            return redirect()->route('sales.show', $invoice->id)->with('success', "Invoice #{$invoice->invoice_no} created and posted successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(SalesInvoice $invoice): View
    {
        $invoice->load(['company', 'customer', 'items.product', 'voucher']);
        return view('sales.show', compact('invoice'));
    }

    public function pos(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $invoiceNo = $this->invoicingService->generateInvoiceNumber($company, $fy, 'pos');
        $customers = Ledger::customers()->where('company_id', $company->id)->orderBy('name')->get();
        $cashLedger = Ledger::where('company_id', $company->id)->where('party_type', 'cash')->first();
        $products = Product::with('taxMaster')->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id', $company->id)->get();

        return view('sales.pos', compact('invoiceNo', 'customers', 'cashLedger', 'products', 'warehouses', 'company', 'fy'));
    }

    public function storePos(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $data = $request->validate([
            'customer_ledger_id' => ['required', 'exists:ledgers,id'],
            'invoice_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'cash_tendered' => ['nullable', 'numeric'],
            'paid_amount' => ['required', 'numeric'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['required', 'numeric'],
        ]);

        $data['company_id'] = $company->id;
        $data['financial_year_id'] = $fy->id;
        $data['invoice_type'] = 'pos';
        $data['change_returned'] = max(0.0, (float)($data['cash_tendered'] ?? 0) - (float)$data['paid_amount']);

        try {
            $invoice = $this->invoicingService->createSalesInvoice($data, $data['items']);

            ActivityLog::log(
                'POS Bill',
                'Sales',
                "POS Bill #{$invoice->invoice_no} for amount ₹ " . number_format($invoice->grand_total, 2)
            );

            return redirect()->route('sales.show', $invoice->id)->with('success', "POS Bill #{$invoice->invoice_no} generated.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
