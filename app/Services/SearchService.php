<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Voucher;

class SearchService
{
    public function searchEverywhere(Company $company, string $query): array
    {
        $q = trim($query);
        if (strlen($q) < 1) {
            return [];
        }

        $results = [];

        // 1. Search Vouchers
        $vouchers = Voucher::where('company_id', $company->id)
            ->where(function ($b) use ($q) {
                $b->where('voucher_no', 'like', "%{$q}%")
                  ->orWhere('reference_no', 'like', "%{$q}%")
                  ->orWhere('narration', 'like', "%{$q}%");
            })
            ->take(5)
            ->get();

        foreach ($vouchers as $v) {
            $results[] = [
                'category' => 'Vouchers',
                'title' => "{$v->voucher_type->value}: {$v->voucher_no}",
                'subtitle' => "Date: {$v->voucher_date->format('d-M-Y')} | Amount: ₹ " . number_format($v->total_amount, 2),
                'url' => route('vouchers.show', $v->id),
                'icon' => 'fa-receipt',
            ];
        }

        // 2. Search Invoices
        $invoices = SalesInvoice::where('company_id', $company->id)
            ->where(function ($b) use ($q) {
                $b->where('invoice_no', 'like', "%{$q}%")
                  ->orWhere('notes', 'like', "%{$q}%");
            })
            ->take(5)
            ->get();

        foreach ($invoices as $inv) {
            $results[] = [
                'category' => 'Sales Invoices',
                'title' => "Invoice #{$inv->invoice_no}",
                'subtitle' => "Grand Total: ₹ " . number_format($inv->grand_total, 2) . " ({$inv->payment_status})",
                'url' => route('sales.show', $inv->id),
                'icon' => 'fa-file-invoice-dollar',
            ];
        }

        // 3. Search Ledgers / Customers / Suppliers
        $ledgers = Ledger::where('company_id', $company->id)
            ->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('gstin', 'like', "%{$q}%");
            })
            ->take(5)
            ->get();

        foreach ($ledgers as $l) {
            $results[] = [
                'category' => ucfirst($l->party_type->value === 'none' ? 'Ledger' : $l->party_type->value),
                'title' => $l->name,
                'subtitle' => "GSTIN: " . ($l->gstin ?: 'N/A') . " | Phone: " . ($l->phone ?: 'N/A'),
                'url' => route('ledgers.statement', $l->id),
                'icon' => 'fa-book',
            ];
        }

        // 4. Search Products
        $products = Product::where('company_id', $company->id)
            ->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%")
                  ->orWhere('hsn_code', 'like', "%{$q}%");
            })
            ->take(5)
            ->get();

        foreach ($products as $p) {
            $results[] = [
                'category' => 'Products',
                'title' => $p->name,
                'subtitle' => "Stock: {$p->current_stock} | Rate: ₹ " . number_format($p->selling_price, 2) . " | HSN: " . ($p->hsn_code ?: 'N/A'),
                'url' => route('products.edit', $p->id),
                'icon' => 'fa-box',
            ];
        }

        return $results;
    }
}
