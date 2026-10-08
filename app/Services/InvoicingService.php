<?php

namespace App\Services;

use App\Enums\EntryType;
use App\Enums\VoucherType;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\StockMovement;
use App\Models\Voucher;
use App\Models\VoucherItem;
use Illuminate\Support\Facades\DB;

class InvoicingService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected InventoryService $inventoryService,
        protected GstService $gstService
    ) {}

    /**
     * Generate sequential invoice number.
     */
    public function generateInvoiceNumber(Company $company, FinancialYear $fy, string $type = 'tax_invoice'): string
    {
        $prefix = match ($type) {
            'pos' => 'POS',
            'quotation' => 'QUOT',
            'challan' => 'DC',
            default => 'INV',
        };

        $yearSlug = date('y', strtotime($fy->start_date->format('Y-m-d'))) . '-' . date('y', strtotime($fy->end_date->format('Y-m-d')));
        $count = SalesInvoice::where('company_id', $company->id)
            ->where('financial_year_id', $fy->id)
            ->where('invoice_type', $type)
            ->count();

        $seq = str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
        return "{$prefix}/{$yearSlug}/{$seq}";
    }

    /**
     * Create a Sales Invoice, its items, deduction from inventory, and balanced Sales Accounting Voucher.
     */
    public function createSalesInvoice(array $header, array $items = []): SalesInvoice
    {
        if (empty($items) && isset($header['items'])) {
            $items = $header['items'];
            unset($header['items']);
        }

        return DB::transaction(function () use ($header, $items) {
            $company = Company::findOrFail($header['company_id']);
            $fy = FinancialYear::findOrFail($header['financial_year_id']);
            $customer = Ledger::findOrFail($header['customer_ledger_id']);

            $isInterstate = $this->gstService->isInterstate($company, $customer->state_code);

            $subtotal = 0.0;
            $taxableAmount = 0.0;
            $totalCgst = 0.0;
            $totalSgst = 0.0;
            $totalIgst = 0.0;

            $processedItems = [];

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemSub = round($qty * $price, 2);
                $discountAmt = (float) ($item['discount_amount'] ?? 0);
                $taxable = round($itemSub - $discountAmt, 2);
                $rate = (float) ($item['gst_rate'] ?? 0);

                $taxCalc = $this->gstService->calculateTaxes($taxable, $rate, $isInterstate);

                $subtotal += $itemSub;
                $taxableAmount += $taxable;
                $totalCgst += $taxCalc['cgst_amount'];
                $totalSgst += $taxCalc['sgst_amount'];
                $totalIgst += $taxCalc['igst_amount'];

                $processedItems[] = array_merge($item, [
                    'subtotal' => $itemSub,
                    'taxable_amount' => $taxable,
                    'cgst_amount' => $taxCalc['cgst_amount'],
                    'sgst_amount' => $taxCalc['sgst_amount'],
                    'igst_amount' => $taxCalc['igst_amount'],
                    'total_amount' => round($taxable + $taxCalc['total_tax'], 2),
                ]);
            }

            $rawGrandTotal = $taxableAmount + $totalCgst + $totalSgst + $totalIgst;
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $paid = (float) ($header['paid_amount'] ?? 0);
            $due = max(0.0, round($roundedGrandTotal - $paid, 2));
            $paymentStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            // Generate invoice number if not provided
            if (empty($header['invoice_no'])) {
                $header['invoice_no'] = $this->generateInvoiceNumber($company, $fy, $header['invoice_type'] ?? 'tax_invoice');
            }

            $invoice = SalesInvoice::create([
                'company_id' => $company->id,
                'financial_year_id' => $fy->id,
                'customer_ledger_id' => $customer->id,
                'shipping_name' => !empty($header['shipping_name']) ? trim($header['shipping_name']) : null,
                'shipping_phone' => !empty($header['shipping_phone']) ? trim($header['shipping_phone']) : null,
                'shipping_email' => !empty($header['shipping_email']) ? trim($header['shipping_email']) : null,
                'shipping_gstin' => !empty($header['shipping_gstin']) ? strtoupper(trim($header['shipping_gstin'])) : null,
                'shipping_address' => !empty($header['shipping_address']) ? trim($header['shipping_address']) : null,
                'shipping_city' => !empty($header['shipping_city']) ? trim($header['shipping_city']) : null,
                'shipping_state' => !empty($header['shipping_state']) ? trim($header['shipping_state']) : null,
                'shipping_state_code' => !empty($header['shipping_state_code']) ? trim($header['shipping_state_code']) : null,
                'shipping_pincode' => !empty($header['shipping_pincode']) ? trim($header['shipping_pincode']) : null,
                'invoice_type' => $header['invoice_type'] ?? 'tax_invoice',
                'invoice_no' => $header['invoice_no'],
                'invoice_date' => $header['invoice_date'] ?? now()->toDateString(),
                'due_date' => $header['due_date'] ?? now()->addDays(15)->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => (float) ($header['discount_amount'] ?? 0),
                'taxable_amount' => $taxableAmount,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
                'payment_method' => $header['payment_method'] ?? 'cash',
                'cash_tendered' => (float) ($header['cash_tendered'] ?? 0),
                'change_returned' => (float) ($header['change_returned'] ?? 0),
                'notes' => $header['notes'] ?? null,
                'terms_conditions' => $header['terms_conditions'] ?? null,
            ]);

            // Save items and record stock movement
            foreach ($processedItems as $pItem) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $pItem['product_id'] ?? null,
                    'warehouse_id' => $pItem['warehouse_id'] ?? null,
                    'description' => trim((string)($pItem['description'] ?? '')),
                    'hsn_code' => $pItem['hsn_code'] ?? null,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'discount_percent' => $pItem['discount_percent'] ?? 0,
                    'discount_amount' => $pItem['discount_amount'] ?? 0,
                    'taxable_amount' => $pItem['taxable_amount'],
                    'gst_rate' => $pItem['gst_rate'] ?? 0,
                    'cgst_amount' => $pItem['cgst_amount'],
                    'sgst_amount' => $pItem['sgst_amount'],
                    'igst_amount' => $pItem['igst_amount'],
                    'total_amount' => $pItem['total_amount'],
                ]);

                if (!empty($pItem['product_id'])) {
                    $product = Product::with('inventoryComponents')->find($pItem['product_id']);

                    // If product has linked inventory components, automatically deduct stock for all components
                    if ($product && $product->has_inventory_components && $product->inventoryComponents->isNotEmpty()) {
                        foreach ($product->inventoryComponents as $comp) {
                            if (!empty($comp->component_product_id)) {
                                $consumedQty = round((float) $pItem['quantity'] * (float) $comp->quantity, 2);
                                if ($consumedQty > 0) {
                                    $this->inventoryService->recordStockMovement(
                                        $company->id,
                                        $comp->component_product_id,
                                        $pItem['warehouse_id'] ?? null,
                                        null,
                                        'outward',
                                        $consumedQty,
                                        (float) $comp->unit_price,
                                        $invoice->invoice_date->format('Y-m-d'),
                                        "Component consumed for {$product->name} (Sales Invoice #{$invoice->invoice_no})"
                                    );
                                }
                            }
                        }
                    }

                    // Record outward stock movement for parent product
                    $this->inventoryService->recordStockMovement(
                        $company->id,
                        $pItem['product_id'],
                        $pItem['warehouse_id'] ?? null,
                        null,
                        'outward',
                        (float) $pItem['quantity'],
                        (float) $pItem['unit_price'],
                        $invoice->invoice_date->format('Y-m-d'),
                        "Sales Invoice #{$invoice->invoice_no}"
                    );
                }
            }

            // Create balanced accounting Sales Voucher (F8)
            $salesAccount = Ledger::where('company_id', $company->id)
                ->where('name', 'Sales Account')
                ->first();

            if ($salesAccount) {
                $voucherNo = $this->accountingService->getNextVoucherNumber($company, $fy, VoucherType::SALES);

                $voucherItems = [
                    // Debit Customer/Party or Cash
                    [
                        'ledger_id' => $invoice->isPos() && $paid > 0 ? (Ledger::where('company_id', $company->id)->where('party_type', 'cash')->first()?->id ?? $customer->id) : $customer->id,
                        'entry_type' => 'debit',
                        'amount' => $roundedGrandTotal,
                        'narration' => "Sales Invoice {$invoice->invoice_no}",
                    ],
                    // Credit Sales Account
                    [
                        'ledger_id' => $salesAccount->id,
                        'entry_type' => 'credit',
                        'amount' => $taxableAmount,
                        'narration' => "Goods/Services sold",
                    ],
                ];

                // Credit Taxes if any
                $cgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output CGST')->first();
                $sgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output SGST')->first();
                $igstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output IGST')->first();

                if ($totalCgst > 0 && $cgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $cgstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalCgst,
                        'narration' => 'Output CGST',
                    ];
                }
                if ($totalSgst > 0 && $sgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $sgstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalSgst,
                        'narration' => 'Output SGST',
                    ];
                }
                if ($totalIgst > 0 && $igstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $igstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalIgst,
                        'narration' => 'Output IGST',
                    ];
                }

                // Round-off entry if applicable
                if (abs($roundOff) > 0) {
                    $roundOffLedger = Ledger::where('company_id', $company->id)->where('name', 'Round Off Account')->first();
                    if ($roundOffLedger) {
                        $voucherItems[] = [
                            'ledger_id' => $roundOffLedger->id,
                            'entry_type' => $roundOff > 0 ? 'credit' : 'debit',
                            'amount' => abs($roundOff),
                            'narration' => 'Invoice round off adjustment',
                        ];
                    }
                }

                $voucher = $this->accountingService->createVoucher([
                    'company_id' => $company->id,
                    'financial_year_id' => $fy->id,
                    'voucher_type' => VoucherType::SALES,
                    'voucher_no' => $voucherNo,
                    'voucher_date' => $invoice->invoice_date->format('Y-m-d'),
                    'reference_no' => $invoice->invoice_no,
                    'party_ledger_id' => $customer->id,
                    'narration' => "Sales Invoice {$invoice->invoice_no} to {$customer->name}",
                    'status' => 'posted',
                ], $voucherItems);

                $invoice->update(['voucher_id' => $voucher->id]);
            }

            return $invoice;
        });
    }

    /**
     * Create a Purchase Invoice, item inward stock movements, and balanced Purchase Accounting Voucher.
     */
    public function createPurchaseInvoice(array $header, array $items = []): PurchaseInvoice
    {
        if (empty($items) && isset($header['items'])) {
            $items = $header['items'];
            unset($header['items']);
        }

        return DB::transaction(function () use ($header, $items) {
            $company = Company::findOrFail($header['company_id']);
            $fy = FinancialYear::findOrFail($header['financial_year_id']);
            $supplier = Ledger::findOrFail($header['supplier_ledger_id']);

            $isInterstate = $this->gstService->isInterstate($company, $supplier->state_code);

            $subtotal = 0.0;
            $taxableAmount = 0.0;
            $totalCgst = 0.0;
            $totalSgst = 0.0;
            $totalIgst = 0.0;

            $processedItems = [];

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemSub = round($qty * $price, 2);
                $discountAmt = (float) ($item['discount_amount'] ?? 0);
                $taxable = round($itemSub - $discountAmt, 2);
                $rate = (float) ($item['gst_rate'] ?? 0);

                $taxCalc = $this->gstService->calculateTaxes($taxable, $rate, $isInterstate);

                $subtotal += $itemSub;
                $taxableAmount += $taxable;
                $totalCgst += $taxCalc['cgst_amount'];
                $totalSgst += $taxCalc['sgst_amount'];
                $totalIgst += $taxCalc['igst_amount'];

                $processedItems[] = array_merge($item, [
                    'subtotal' => $itemSub,
                    'taxable_amount' => $taxable,
                    'cgst_amount' => $taxCalc['cgst_amount'],
                    'sgst_amount' => $taxCalc['sgst_amount'],
                    'igst_amount' => $taxCalc['igst_amount'],
                    'total_amount' => round($taxable + $taxCalc['total_tax'], 2),
                ]);
            }

            $rawGrandTotal = $taxableAmount + $totalCgst + $totalSgst + $totalIgst;
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $paid = (float) ($header['paid_amount'] ?? 0);
            $due = max(0.0, round($roundedGrandTotal - $paid, 2));
            $paymentStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $invoice = PurchaseInvoice::create([
                'company_id' => $company->id,
                'financial_year_id' => $fy->id,
                'supplier_ledger_id' => $supplier->id,
                'bill_no' => $header['bill_no'],
                'bill_date' => $header['bill_date'] ?? now()->toDateString(),
                'due_date' => $header['due_date'] ?? now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => (float) ($header['discount_amount'] ?? 0),
                'taxable_amount' => $taxableAmount,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
                'notes' => $header['notes'] ?? null,
            ]);

            // Save items & inward stock
            foreach ($processedItems as $pItem) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $pItem['product_id'] ?? null,
                    'warehouse_id' => $pItem['warehouse_id'] ?? null,
                    'description' => trim((string)($pItem['description'] ?? '')),
                    'hsn_code' => $pItem['hsn_code'] ?? null,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'discount_amount' => $pItem['discount_amount'] ?? 0,
                    'taxable_amount' => $pItem['taxable_amount'],
                    'gst_rate' => $pItem['gst_rate'] ?? 0,
                    'cgst_amount' => $pItem['cgst_amount'],
                    'sgst_amount' => $pItem['sgst_amount'],
                    'igst_amount' => $pItem['igst_amount'],
                    'total_amount' => $pItem['total_amount'],
                ]);

                if (!empty($pItem['product_id'])) {
                    $this->inventoryService->recordStockMovement(
                        $company->id,
                        $pItem['product_id'],
                        $pItem['warehouse_id'] ?? null,
                        null,
                        'inward',
                        (float) $pItem['quantity'],
                        (float) $pItem['unit_price'],
                        $invoice->bill_date->format('Y-m-d'),
                        "Purchase Bill #{$invoice->bill_no}"
                    );
                }
            }

            // Balanced accounting Purchase Voucher (F9)
            $purchaseAccount = Ledger::where('company_id', $company->id)
                ->where('name', 'Purchase Account')
                ->first();

            if ($purchaseAccount) {
                $voucherNo = $this->accountingService->getNextVoucherNumber($company, $fy, VoucherType::PURCHASE);

                $voucherItems = [
                    // Debit Purchase Account
                    [
                        'ledger_id' => $purchaseAccount->id,
                        'entry_type' => 'debit',
                        'amount' => $taxableAmount,
                        'narration' => "Purchase from {$supplier->name}",
                    ],
                ];

                // Debit Input Taxes
                $cgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input CGST')->first();
                $sgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input SGST')->first();
                $igstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input IGST')->first();

                if ($totalCgst > 0 && $cgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $cgstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalCgst,
                        'narration' => 'Input CGST',
                    ];
                }
                if ($totalSgst > 0 && $sgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $sgstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalSgst,
                        'narration' => 'Input SGST',
                    ];
                }
                if ($totalIgst > 0 && $igstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $igstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalIgst,
                        'narration' => 'Input IGST',
                    ];
                }

                // Credit Supplier / Party
                $voucherItems[] = [
                    'ledger_id' => $supplier->id,
                    'entry_type' => 'credit',
                    'amount' => $roundedGrandTotal,
                    'narration' => "Bill #{$invoice->bill_no}",
                ];

                // Round off if any
                if (abs($roundOff) > 0) {
                    $roundOffLedger = Ledger::where('company_id', $company->id)->where('name', 'Round Off Account')->first();
                    if ($roundOffLedger) {
                        $voucherItems[] = [
                            'ledger_id' => $roundOffLedger->id,
                            'entry_type' => $roundOff > 0 ? 'debit' : 'credit',
                            'amount' => abs($roundOff),
                            'narration' => 'Purchase bill round off',
                        ];
                    }
                }

                $voucher = $this->accountingService->createVoucher([
                    'company_id' => $company->id,
                    'financial_year_id' => $fy->id,
                    'voucher_type' => VoucherType::PURCHASE,
                    'voucher_no' => $voucherNo,
                    'voucher_date' => $invoice->bill_date->format('Y-m-d'),
                    'reference_no' => $invoice->bill_no,
                    'party_ledger_id' => $supplier->id,
                    'narration' => "Purchase Bill {$invoice->bill_no} from {$supplier->name}",
                    'status' => 'posted',
                ], $voucherItems);

                $invoice->update(['voucher_id' => $voucher->id]);
            }

            return $invoice;
        });
    }

    /**
     * Update a Sales Invoice, reconcile items, adjust stock movements, and update balanced Sales Accounting Voucher.
     */
    public function updateSalesInvoice(SalesInvoice $invoice, array $header, array $items = []): SalesInvoice
    {
        if (empty($items) && isset($header['items'])) {
            $items = $header['items'];
            unset($header['items']);
        }

        return DB::transaction(function () use ($invoice, $header, $items) {
            $company = Company::findOrFail($header['company_id'] ?? $invoice->company_id);
            $fy = FinancialYear::findOrFail($header['financial_year_id'] ?? $invoice->financial_year_id);
            $customer = Ledger::findOrFail($header['customer_ledger_id']);

            $isInterstate = $this->gstService->isInterstate($company, $customer->state_code);

            $subtotal = 0.0;
            $taxableAmount = 0.0;
            $totalCgst = 0.0;
            $totalSgst = 0.0;
            $totalIgst = 0.0;

            $processedItems = [];

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemSub = round($qty * $price, 2);
                $discountAmt = (float) ($item['discount_amount'] ?? 0);
                $taxable = round($itemSub - $discountAmt, 2);
                $rate = (float) ($item['gst_rate'] ?? 0);

                $taxCalc = $this->gstService->calculateTaxes($taxable, $rate, $isInterstate);

                $subtotal += $itemSub;
                $taxableAmount += $taxable;
                $totalCgst += $taxCalc['cgst_amount'];
                $totalSgst += $taxCalc['sgst_amount'];
                $totalIgst += $taxCalc['igst_amount'];

                $processedItems[] = array_merge($item, [
                    'subtotal' => $itemSub,
                    'taxable_amount' => $taxable,
                    'cgst_amount' => $taxCalc['cgst_amount'],
                    'sgst_amount' => $taxCalc['sgst_amount'],
                    'igst_amount' => $taxCalc['igst_amount'],
                    'total_amount' => round($taxable + $taxCalc['total_tax'], 2),
                ]);
            }

            $rawGrandTotal = $taxableAmount + $totalCgst + $totalSgst + $totalIgst;
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $paid = (float) ($header['paid_amount'] ?? $invoice->paid_amount ?? 0);
            $due = max(0.0, round($roundedGrandTotal - $paid, 2));
            $paymentStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            // 1. Reverse and delete old stock movements for this invoice
            $oldMovements = StockMovement::where('company_id', $company->id)
                ->where(function ($q) use ($invoice) {
                    $q->where('narration', 'like', "%Sales Invoice #{$invoice->invoice_no}%");
                    if ($invoice->voucher_id) {
                        $q->orWhere('voucher_id', $invoice->voucher_id);
                    }
                })->get();

            $affectedProductIds = $oldMovements->pluck('product_id')->unique()->filter()->values();

            if ($oldMovements->isNotEmpty()) {
                StockMovement::whereIn('id', $oldMovements->pluck('id'))->delete();
                foreach ($affectedProductIds as $pId) {
                    $p = Product::find($pId);
                    if ($p) {
                        $this->inventoryService->recalculateProductStock($p);
                    }
                }
            }

            // 2. Delete old invoice items
            $invoice->items()->delete();

            // 3. Update invoice header
            $invoice->update([
                'customer_ledger_id' => $customer->id,
                'shipping_name' => !empty($header['shipping_name']) ? trim($header['shipping_name']) : null,
                'shipping_phone' => !empty($header['shipping_phone']) ? trim($header['shipping_phone']) : null,
                'shipping_email' => !empty($header['shipping_email']) ? trim($header['shipping_email']) : null,
                'shipping_gstin' => !empty($header['shipping_gstin']) ? strtoupper(trim($header['shipping_gstin'])) : null,
                'shipping_address' => !empty($header['shipping_address']) ? trim($header['shipping_address']) : null,
                'shipping_city' => !empty($header['shipping_city']) ? trim($header['shipping_city']) : null,
                'shipping_state' => !empty($header['shipping_state']) ? trim($header['shipping_state']) : null,
                'shipping_state_code' => !empty($header['shipping_state_code']) ? trim($header['shipping_state_code']) : null,
                'shipping_pincode' => !empty($header['shipping_pincode']) ? trim($header['shipping_pincode']) : null,
                'invoice_date' => $header['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $header['due_date'] ?? $invoice->due_date,
                'subtotal' => $subtotal,
                'discount_amount' => (float) ($header['discount_amount'] ?? 0),
                'taxable_amount' => $taxableAmount,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
                'payment_method' => $header['payment_method'] ?? $invoice->payment_method,
                'cash_tendered' => (float) ($header['cash_tendered'] ?? $invoice->cash_tendered),
                'change_returned' => (float) ($header['change_returned'] ?? $invoice->change_returned),
                'notes' => $header['notes'] ?? null,
                'terms_conditions' => $header['terms_conditions'] ?? null,
            ]);

            // 4. Save new items and record fresh stock movements
            foreach ($processedItems as $pItem) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $pItem['product_id'] ?? null,
                    'warehouse_id' => $pItem['warehouse_id'] ?? null,
                    'description' => trim((string)($pItem['description'] ?? '')),
                    'hsn_code' => $pItem['hsn_code'] ?? null,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'discount_percent' => $pItem['discount_percent'] ?? 0,
                    'discount_amount' => $pItem['discount_amount'] ?? 0,
                    'taxable_amount' => $pItem['taxable_amount'],
                    'gst_rate' => $pItem['gst_rate'] ?? 0,
                    'cgst_amount' => $pItem['cgst_amount'],
                    'sgst_amount' => $pItem['sgst_amount'],
                    'igst_amount' => $pItem['igst_amount'],
                    'total_amount' => $pItem['total_amount'],
                ]);

                if (!empty($pItem['product_id'])) {
                    $product = Product::with('inventoryComponents')->find($pItem['product_id']);

                    if ($product && $product->has_inventory_components && $product->inventoryComponents->isNotEmpty()) {
                        foreach ($product->inventoryComponents as $comp) {
                            if (!empty($comp->component_product_id)) {
                                $consumedQty = round((float) $pItem['quantity'] * (float) $comp->quantity, 2);
                                if ($consumedQty > 0) {
                                    $this->inventoryService->recordStockMovement(
                                        $company->id,
                                        $comp->component_product_id,
                                        $pItem['warehouse_id'] ?? null,
                                        $invoice->voucher_id,
                                        'outward',
                                        $consumedQty,
                                        (float) $comp->unit_price,
                                        $invoice->invoice_date->format('Y-m-d'),
                                        "Component consumed for {$product->name} (Sales Invoice #{$invoice->invoice_no})"
                                    );
                                }
                            }
                        }
                    }

                    $this->inventoryService->recordStockMovement(
                        $company->id,
                        $pItem['product_id'],
                        $pItem['warehouse_id'] ?? null,
                        $invoice->voucher_id,
                        'outward',
                        (float) $pItem['quantity'],
                        (float) $pItem['unit_price'],
                        $invoice->invoice_date->format('Y-m-d'),
                        "Sales Invoice #{$invoice->invoice_no}"
                    );
                }
            }

            // 5. Update balanced Accounting Voucher
            $salesAccount = Ledger::where('company_id', $company->id)
                ->where('name', 'Sales Account')
                ->first();

            if ($salesAccount) {
                $voucherItems = [
                    [
                        'ledger_id' => $invoice->isPos() && $paid > 0 ? (Ledger::where('company_id', $company->id)->where('party_type', 'cash')->first()?->id ?? $customer->id) : $customer->id,
                        'entry_type' => 'debit',
                        'amount' => $roundedGrandTotal,
                        'narration' => "Sales Invoice {$invoice->invoice_no}",
                    ],
                    [
                        'ledger_id' => $salesAccount->id,
                        'entry_type' => 'credit',
                        'amount' => $taxableAmount,
                        'narration' => "Goods/Services sold",
                    ],
                ];

                $cgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output CGST')->first();
                $sgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output SGST')->first();
                $igstLedger = Ledger::where('company_id', $company->id)->where('name', 'Output IGST')->first();

                if ($totalCgst > 0 && $cgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $cgstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalCgst,
                        'narration' => 'Output CGST',
                    ];
                }
                if ($totalSgst > 0 && $sgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $sgstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalSgst,
                        'narration' => 'Output SGST',
                    ];
                }
                if ($totalIgst > 0 && $igstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $igstLedger->id,
                        'entry_type' => 'credit',
                        'amount' => $totalIgst,
                        'narration' => 'Output IGST',
                    ];
                }

                if (abs($roundOff) > 0) {
                    $roundOffLedger = Ledger::where('company_id', $company->id)->where('name', 'Round Off Account')->first();
                    if ($roundOffLedger) {
                        $voucherItems[] = [
                            'ledger_id' => $roundOffLedger->id,
                            'entry_type' => $roundOff > 0 ? 'credit' : 'debit',
                            'amount' => abs($roundOff),
                            'narration' => 'Invoice round off adjustment',
                        ];
                    }
                }

                if ($invoice->voucher) {
                    $voucher = $invoice->voucher;
                    $this->accountingService->reverseLedgerEntries($voucher);
                    $voucher->items()->delete();
                    $voucher->update([
                        'voucher_date' => $invoice->invoice_date->format('Y-m-d'),
                        'reference_no' => $invoice->invoice_no,
                        'party_ledger_id' => $customer->id,
                        'narration' => "Sales Invoice {$invoice->invoice_no} to {$customer->name}",
                        'total_amount' => $roundedGrandTotal,
                    ]);

                    foreach ($voucherItems as $index => $vItem) {
                        VoucherItem::create([
                            'voucher_id' => $voucher->id,
                            'ledger_id' => $vItem['ledger_id'],
                            'entry_type' => $vItem['entry_type'],
                            'amount' => $vItem['amount'],
                            'narration' => $vItem['narration'] ?? null,
                            'line_order' => $index + 1,
                        ]);
                    }

                    $this->accountingService->postLedgerEntries($voucher);
                } else {
                    $voucherNo = $this->accountingService->getNextVoucherNumber($company, $fy, VoucherType::SALES);
                    $voucher = $this->accountingService->createVoucher([
                        'company_id' => $company->id,
                        'financial_year_id' => $fy->id,
                        'voucher_type' => VoucherType::SALES,
                        'voucher_no' => $voucherNo,
                        'voucher_date' => $invoice->invoice_date->format('Y-m-d'),
                        'reference_no' => $invoice->invoice_no,
                        'party_ledger_id' => $customer->id,
                        'narration' => "Sales Invoice {$invoice->invoice_no} to {$customer->name}",
                        'status' => 'posted',
                    ], $voucherItems);

                    $invoice->update(['voucher_id' => $voucher->id]);
                }
            }

            return $invoice;
        });
    }

    /**
     * Update a Purchase Invoice, reconcile inward stock movements, and update balanced Purchase Accounting Voucher.
     */
    public function updatePurchaseInvoice(PurchaseInvoice $invoice, array $header, array $items = []): PurchaseInvoice
    {
        if (empty($items) && isset($header['items'])) {
            $items = $header['items'];
            unset($header['items']);
        }

        return DB::transaction(function () use ($invoice, $header, $items) {
            $company = Company::findOrFail($header['company_id'] ?? $invoice->company_id);
            $fy = FinancialYear::findOrFail($header['financial_year_id'] ?? $invoice->financial_year_id);
            $supplier = Ledger::findOrFail($header['supplier_ledger_id']);

            $isInterstate = $this->gstService->isInterstate($company, $supplier->state_code);

            $subtotal = 0.0;
            $taxableAmount = 0.0;
            $totalCgst = 0.0;
            $totalSgst = 0.0;
            $totalIgst = 0.0;

            $processedItems = [];

            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $itemSub = round($qty * $price, 2);
                $discountAmt = (float) ($item['discount_amount'] ?? 0);
                $taxable = round($itemSub - $discountAmt, 2);
                $rate = (float) ($item['gst_rate'] ?? 0);

                $taxCalc = $this->gstService->calculateTaxes($taxable, $rate, $isInterstate);

                $subtotal += $itemSub;
                $taxableAmount += $taxable;
                $totalCgst += $taxCalc['cgst_amount'];
                $totalSgst += $taxCalc['sgst_amount'];
                $totalIgst += $taxCalc['igst_amount'];

                $processedItems[] = array_merge($item, [
                    'subtotal' => $itemSub,
                    'taxable_amount' => $taxable,
                    'cgst_amount' => $taxCalc['cgst_amount'],
                    'sgst_amount' => $taxCalc['sgst_amount'],
                    'igst_amount' => $taxCalc['igst_amount'],
                    'total_amount' => round($taxable + $taxCalc['total_tax'], 2),
                ]);
            }

            $rawGrandTotal = $taxableAmount + $totalCgst + $totalSgst + $totalIgst;
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $paid = (float) ($header['paid_amount'] ?? $invoice->paid_amount ?? 0);
            $due = max(0.0, round($roundedGrandTotal - $paid, 2));
            $paymentStatus = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            // 1. Reverse old stock movements
            $oldMovements = StockMovement::where('company_id', $company->id)
                ->where(function ($q) use ($invoice) {
                    $q->where('narration', 'like', "%Purchase Bill #{$invoice->bill_no}%");
                    if ($invoice->voucher_id) {
                        $q->orWhere('voucher_id', $invoice->voucher_id);
                    }
                })->get();

            $affectedProductIds = $oldMovements->pluck('product_id')->unique()->filter()->values();

            if ($oldMovements->isNotEmpty()) {
                StockMovement::whereIn('id', $oldMovements->pluck('id'))->delete();
                foreach ($affectedProductIds as $pId) {
                    $p = Product::find($pId);
                    if ($p) {
                        $this->inventoryService->recalculateProductStock($p);
                    }
                }
            }

            // 2. Delete old invoice items
            $invoice->items()->delete();

            // 3. Update PurchaseInvoice header
            $invoice->update([
                'supplier_ledger_id' => $supplier->id,
                'bill_no' => $header['bill_no'],
                'bill_date' => $header['bill_date'] ?? $invoice->bill_date,
                'due_date' => $header['due_date'] ?? $invoice->due_date,
                'subtotal' => $subtotal,
                'discount_amount' => (float) ($header['discount_amount'] ?? 0),
                'taxable_amount' => $taxableAmount,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $paymentStatus,
                'notes' => $header['notes'] ?? null,
            ]);

            // 4. Save new items & inward stock
            foreach ($processedItems as $pItem) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $pItem['product_id'] ?? null,
                    'warehouse_id' => $pItem['warehouse_id'] ?? null,
                    'description' => trim((string)($pItem['description'] ?? '')),
                    'hsn_code' => $pItem['hsn_code'] ?? null,
                    'quantity' => $pItem['quantity'],
                    'unit_price' => $pItem['unit_price'],
                    'discount_amount' => $pItem['discount_amount'] ?? 0,
                    'taxable_amount' => $pItem['taxable_amount'],
                    'gst_rate' => $pItem['gst_rate'] ?? 0,
                    'cgst_amount' => $pItem['cgst_amount'],
                    'sgst_amount' => $pItem['sgst_amount'],
                    'igst_amount' => $pItem['igst_amount'],
                    'total_amount' => $pItem['total_amount'],
                ]);

                if (!empty($pItem['product_id'])) {
                    $this->inventoryService->recordStockMovement(
                        $company->id,
                        $pItem['product_id'],
                        $pItem['warehouse_id'] ?? null,
                        $invoice->voucher_id,
                        'inward',
                        (float) $pItem['quantity'],
                        (float) $pItem['unit_price'],
                        $invoice->bill_date->format('Y-m-d'),
                        "Purchase Bill #{$invoice->bill_no}"
                    );
                }
            }

            // 5. Update balanced Purchase Accounting Voucher
            $purchaseAccount = Ledger::where('company_id', $company->id)
                ->where('name', 'Purchase Account')
                ->first();

            if ($purchaseAccount) {
                $voucherItems = [
                    [
                        'ledger_id' => $purchaseAccount->id,
                        'entry_type' => 'debit',
                        'amount' => $taxableAmount,
                        'narration' => "Purchase from {$supplier->name}",
                    ],
                ];

                $cgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input CGST')->first();
                $sgstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input SGST')->first();
                $igstLedger = Ledger::where('company_id', $company->id)->where('name', 'Input IGST')->first();

                if ($totalCgst > 0 && $cgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $cgstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalCgst,
                        'narration' => 'Input CGST',
                    ];
                }
                if ($totalSgst > 0 && $sgstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $sgstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalSgst,
                        'narration' => 'Input SGST',
                    ];
                }
                if ($totalIgst > 0 && $igstLedger) {
                    $voucherItems[] = [
                        'ledger_id' => $igstLedger->id,
                        'entry_type' => 'debit',
                        'amount' => $totalIgst,
                        'narration' => 'Input IGST',
                    ];
                }

                $voucherItems[] = [
                    'ledger_id' => $supplier->id,
                    'entry_type' => 'credit',
                    'amount' => $roundedGrandTotal,
                    'narration' => "Bill #{$invoice->bill_no}",
                ];

                if (abs($roundOff) > 0) {
                    $roundOffLedger = Ledger::where('company_id', $company->id)->where('name', 'Round Off Account')->first();
                    if ($roundOffLedger) {
                        $voucherItems[] = [
                            'ledger_id' => $roundOffLedger->id,
                            'entry_type' => $roundOff > 0 ? 'debit' : 'credit',
                            'amount' => abs($roundOff),
                            'narration' => 'Purchase bill round off',
                        ];
                    }
                }

                if ($invoice->voucher) {
                    $voucher = $invoice->voucher;
                    $this->accountingService->reverseLedgerEntries($voucher);
                    $voucher->items()->delete();
                    $voucher->update([
                        'voucher_date' => $invoice->bill_date->format('Y-m-d'),
                        'reference_no' => $invoice->bill_no,
                        'party_ledger_id' => $supplier->id,
                        'narration' => "Purchase Bill {$invoice->bill_no} from {$supplier->name}",
                        'total_amount' => $roundedGrandTotal,
                    ]);

                    foreach ($voucherItems as $index => $vItem) {
                        VoucherItem::create([
                            'voucher_id' => $voucher->id,
                            'ledger_id' => $vItem['ledger_id'],
                            'entry_type' => $vItem['entry_type'],
                            'amount' => $vItem['amount'],
                            'narration' => $vItem['narration'] ?? null,
                            'line_order' => $index + 1,
                        ]);
                    }

                    $this->accountingService->postLedgerEntries($voucher);
                } else {
                    $voucherNo = $this->accountingService->getNextVoucherNumber($company, $fy, VoucherType::PURCHASE);
                    $voucher = $this->accountingService->createVoucher([
                        'company_id' => $company->id,
                        'financial_year_id' => $fy->id,
                        'voucher_type' => VoucherType::PURCHASE,
                        'voucher_no' => $voucherNo,
                        'voucher_date' => $invoice->bill_date->format('Y-m-d'),
                        'reference_no' => $invoice->bill_no,
                        'party_ledger_id' => $supplier->id,
                        'narration' => "Purchase Bill {$invoice->bill_no} from {$supplier->name}",
                        'status' => 'posted',
                    ], $voucherItems);

                    $invoice->update(['voucher_id' => $voucher->id]);
                }
            }

            return $invoice;
        });
    }
}
