<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Ledger;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;

class GstService
{
    /**
     * Determines if a transaction is inter-state based on company and party state codes.
     */
    public function isInterstate(Company $company, ?string $partyStateCode): bool
    {
        if (empty($partyStateCode)) {
            return false; // Default to intra-state if unspecified
        }

        $companyStateCode = trim($company->state_code ?? '');
        $partyStateCode = trim($partyStateCode);

        return $companyStateCode !== $partyStateCode;
    }

    /**
     * Calculates CGST, SGST, IGST breakdown for an item taxable amount and GST percentage rate.
     */
    public function calculateTaxes(float $taxableAmount, float $rate, bool $isInterstate): array
    {
        $totalTax = round(($taxableAmount * $rate) / 100, 2);

        if ($isInterstate) {
            return [
                'is_interstate' => true,
                'rate' => $rate,
                'cgst_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_rate' => 0.0,
                'sgst_amount' => 0.0,
                'igst_rate' => $rate,
                'igst_amount' => $totalTax,
                'total_tax' => $totalTax,
            ];
        }

        $halfRate = round($rate / 2, 2);
        $halfTax = round($totalTax / 2, 2);
        // Ensure rounding doesn't lose a cent
        $otherHalf = round($totalTax - $halfTax, 2);

        return [
            'is_interstate' => false,
            'rate' => $rate,
            'cgst_rate' => $halfRate,
            'cgst_amount' => $halfTax,
            'sgst_rate' => $halfRate,
            'sgst_amount' => $otherHalf,
            'igst_rate' => 0.0,
            'igst_amount' => 0.0,
            'total_tax' => $totalTax,
        ];
    }

    /**
     * Generate GSTR-1 Summary breakdown for filing.
     */
    public function getGstr1Summary(Company $company, string $fromDate, string $toDate): array
    {
        $invoices = SalesInvoice::with(['customer', 'items'])
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->get();

        $b2b = [];
        $b2c = [];
        $hsnSummary = [];

        $totalTaxable = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;
        $totalInvoiceVal = 0.0;

        foreach ($invoices as $inv) {
            $isB2b = !empty($inv->customer?->gstin);

            $data = [
                'invoice_no' => $inv->invoice_no,
                'invoice_date' => $inv->invoice_date->format('d-m-Y'),
                'customer_name' => $inv->customer?->name,
                'customer_gstin' => $inv->customer?->gstin ?? 'URP',
                'place_of_supply' => $inv->customer?->state ?? $company->state,
                'taxable_amount' => (float) $inv->taxable_amount,
                'cgst_amount' => (float) $inv->cgst_amount,
                'sgst_amount' => (float) $inv->sgst_amount,
                'igst_amount' => (float) $inv->igst_amount,
                'grand_total' => (float) $inv->grand_total,
            ];

            if ($isB2b) {
                $b2b[] = $data;
            } else {
                $b2c[] = $data;
            }

            $totalTaxable += (float) $inv->taxable_amount;
            $totalCgst += (float) $inv->cgst_amount;
            $totalSgst += (float) $inv->sgst_amount;
            $totalIgst += (float) $inv->igst_amount;
            $totalInvoiceVal += (float) $inv->grand_total;

            foreach ($inv->items as $item) {
                $hsn = $item->hsn_code ?? 'OTHER';
                if (!isset($hsnSummary[$hsn])) {
                    $hsnSummary[$hsn] = [
                        'hsn' => $hsn,
                        'description' => $item->description,
                        'total_quantity' => 0.0,
                        'taxable_value' => 0.0,
                        'cgst' => 0.0,
                        'sgst' => 0.0,
                        'igst' => 0.0,
                        'total' => 0.0,
                    ];
                }
                $hsnSummary[$hsn]['total_quantity'] += (float) $item->quantity;
                $hsnSummary[$hsn]['taxable_value'] += (float) $item->taxable_amount;
                $hsnSummary[$hsn]['cgst'] += (float) $item->cgst_amount;
                $hsnSummary[$hsn]['sgst'] += (float) $item->sgst_amount;
                $hsnSummary[$hsn]['igst'] += (float) $item->igst_amount;
                $hsnSummary[$hsn]['total'] += (float) $item->total_amount;
            }
        }

        return [
            'total_invoices' => $invoices->count(),
            'total_taxable' => $totalTaxable,
            'total_cgst' => $totalCgst,
            'total_sgst' => $totalSgst,
            'total_igst' => $totalIgst,
            'total_tax' => $totalCgst + $totalSgst + $totalIgst,
            'total_invoice_val' => $totalInvoiceVal,
            'b2b_invoices' => $b2b,
            'b2c_invoices' => $b2c,
            'hsn_summary' => array_values($hsnSummary),
        ];
    }
}
