<?php

namespace App\Services;

use App\Enums\PartyType;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\LedgerGroup;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Day Book: All vouchers entered on or between given dates.
     */
    public function getDayBook(Company $company, string $fromDate, string $toDate, ?string $voucherType = null): array
    {
        $query = Voucher::with(['partyLedger', 'items.ledger'])
            ->where('company_id', $company->id)
            ->whereBetween('voucher_date', [$fromDate, $toDate])
            ->orderBy('voucher_date', 'asc')
            ->orderBy('id', 'asc');

        if ($voucherType) {
            $query->where('voucher_type', $voucherType);
        }

        $vouchers = $query->get();
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($vouchers as $v) {
            $totalDebit += (float) $v->total_amount;
            $totalCredit += (float) $v->total_amount;
        }

        return [
            'vouchers' => $vouchers,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'count' => $vouchers->count(),
        ];
    }

    /**
     * Detailed Ledger Account Statement with opening balance and running Dr/Cr totals.
     */
    public function getLedgerStatement(Ledger $ledger, string $fromDate, string $toDate): array
    {
        $opening = (float) $ledger->opening_balance;
        if ($ledger->opening_balance_type === 'Cr') {
            $opening = -$opening;
        }

        // Add entries prior to $fromDate to calculate starting balance for the period
        $priorDebits = (float) LedgerEntry::where('ledger_id', $ledger->id)
            ->where('entry_date', '<', $fromDate)
            ->where('entry_type', 'debit')
            ->sum('amount');

        $priorCredits = (float) LedgerEntry::where('ledger_id', $ledger->id)
            ->where('entry_date', '<', $fromDate)
            ->where('entry_type', 'credit')
            ->sum('amount');

        $periodOpeningBalance = $opening + ($priorDebits - $priorCredits);

        // Entries within the selected date window
        $entries = LedgerEntry::with('voucher')
            ->where('ledger_id', $ledger->id)
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = $periodOpeningBalance;
        $totalPeriodDebits = 0.0;
        $totalPeriodCredits = 0.0;

        $formattedEntries = [];
        foreach ($entries as $entry) {
            $amt = (float) $entry->amount;
            if ($entry->entry_type->value === 'debit') {
                $running += $amt;
                $totalPeriodDebits += $amt;
            } else {
                $running -= $amt;
                $totalPeriodCredits += $amt;
            }

            $formattedEntries[] = [
                'entry' => $entry,
                'debit' => $entry->entry_type->value === 'debit' ? $amt : 0.0,
                'credit' => $entry->entry_type->value === 'credit' ? $amt : 0.0,
                'running_balance' => $running,
                'running_type' => $running >= 0 ? 'Dr' : 'Cr',
            ];
        }

        return [
            'ledger' => $ledger,
            'opening_balance' => $periodOpeningBalance,
            'opening_type' => $periodOpeningBalance >= 0 ? 'Dr' : 'Cr',
            'entries' => $formattedEntries,
            'total_debit' => $totalPeriodDebits,
            'total_credit' => $totalPeriodCredits,
            'closing_balance' => $running,
            'closing_type' => $running >= 0 ? 'Dr' : 'Cr',
        ];
    }

    /**
     * Trial Balance: List all ledgers grouped by Master Group with debit & credit columns.
     */
    public function getTrialBalance(Company $company, string $asOfDate): array
    {
        $ledgers = Ledger::with('group')
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($ledgers as $ledger) {
            $opening = (float) $ledger->opening_balance;
            if ($ledger->opening_balance_type === 'Cr') {
                $opening = -$opening;
            }

            $debits = (float) LedgerEntry::where('ledger_id', $ledger->id)
                ->where('entry_date', '<=', $asOfDate)
                ->where('entry_type', 'debit')
                ->sum('amount');

            $credits = (float) LedgerEntry::where('ledger_id', $ledger->id)
                ->where('entry_date', '<=', $asOfDate)
                ->where('entry_type', 'credit')
                ->sum('amount');

            $net = $opening + ($debits - $credits);

            if (round($net, 2) != 0.0) {
                $debitVal = $net > 0 ? abs($net) : 0.0;
                $creditVal = $net < 0 ? abs($net) : 0.0;

                $totalDebit += $debitVal;
                $totalCredit += $creditVal;

                $rows[] = [
                    'ledger_id' => $ledger->id,
                    'ledger_name' => $ledger->name,
                    'group_name' => $ledger->group?->name ?? 'Primary',
                    'nature' => $ledger->group?->nature?->value ?? 'ASSET',
                    'debit' => $debitVal,
                    'credit' => $creditVal,
                ];
            }
        }

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_matched' => round($totalDebit, 2) === round($totalCredit, 2),
            'difference' => abs(round($totalDebit - $totalCredit, 2)),
        ];
    }

    /**
     * Profit & Loss Account (Trading & P&L Statement):
     * Trading: Direct Incomes - Direct Expenses = Gross Profit
     * P&L: Gross Profit + Indirect Incomes - Indirect Expenses = Net Profit
     */
    public function getProfitAndLoss(Company $company, string $fromDate, string $toDate): array
    {
        $groups = LedgerGroup::with(['ledgers.entries' => function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('entry_date', [$fromDate, $toDate]);
        }])->get();

        $directIncomes = 0.0;
        $directExpenses = 0.0;
        $indirectIncomes = 0.0;
        $indirectExpenses = 0.0;

        $incomeRows = [];
        $expenseRows = [];

        foreach ($groups as $group) {
            foreach ($group->ledgers->where('company_id', $company->id) as $ledger) {
                $debits = $ledger->entries->where('entry_type.value', 'debit')->sum('amount');
                $credits = $ledger->entries->where('entry_type.value', 'credit')->sum('amount');

                $nature = $group->nature?->value ?? 'INCOME';
                $affectsGross = $group->affects_gross_profit;

                if ($nature === 'INCOME') {
                    $net = $credits - $debits;
                    if (round($net, 2) != 0.0) {
                        $incomeRows[] = ['ledger' => $ledger->name, 'group' => $group->name, 'amount' => $net, 'affects_gross' => $affectsGross];
                        if ($affectsGross) {
                            $directIncomes += $net;
                        } else {
                            $indirectIncomes += $net;
                        }
                    }
                } elseif ($nature === 'EXPENSE') {
                    $net = $debits - $credits;
                    if (round($net, 2) != 0.0) {
                        $expenseRows[] = ['ledger' => $ledger->name, 'group' => $group->name, 'amount' => $net, 'affects_gross' => $affectsGross];
                        if ($affectsGross) {
                            $directExpenses += $net;
                        } else {
                            $indirectExpenses += $net;
                        }
                    }
                }
            }
        }

        $grossProfit = $directIncomes - $directExpenses;
        $netProfit = $grossProfit + $indirectIncomes - $indirectExpenses;

        return [
            'direct_incomes' => $directIncomes,
            'direct_expenses' => $directExpenses,
            'gross_profit' => $grossProfit,
            'indirect_incomes' => $indirectIncomes,
            'indirect_expenses' => $indirectExpenses,
            'net_profit' => $netProfit,
            'income_rows' => $incomeRows,
            'expense_rows' => $expenseRows,
        ];
    }

    /**
     * Balance Sheet: Assets vs Liabilities with Net Profit integration.
     */
    public function getBalanceSheet(Company $company, string $asOfDate): array
    {
        $ledgers = Ledger::with('group')
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $assetRows = [];
        $liabilityRows = [];
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;

        foreach ($ledgers as $ledger) {
            $nature = $ledger->group?->nature?->value ?? 'ASSET';
            if (!in_array($nature, ['ASSET', 'LIABILITY'])) {
                continue;
            }

            $opening = (float) $ledger->opening_balance;
            if ($ledger->opening_balance_type === 'Cr') {
                $opening = -$opening;
            }

            $debits = (float) LedgerEntry::where('ledger_id', $ledger->id)
                ->where('entry_date', '<=', $asOfDate)
                ->where('entry_type', 'debit')
                ->sum('amount');

            $credits = (float) LedgerEntry::where('ledger_id', $ledger->id)
                ->where('entry_date', '<=', $asOfDate)
                ->where('entry_type', 'credit')
                ->sum('amount');

            $net = $opening + ($debits - $credits);

            if ($nature === 'ASSET') {
                $val = $net; // positive is Debit
                if (round($val, 2) != 0.0) {
                    $assetRows[] = ['ledger' => $ledger->name, 'group' => $ledger->group?->name, 'amount' => $val];
                    $totalAssets += $val;
                }
            } else {
                $val = -$net; // positive is Credit
                if (round($val, 2) != 0.0) {
                    $liabilityRows[] = ['ledger' => $ledger->name, 'group' => $ledger->group?->name, 'amount' => $val];
                    $totalLiabilities += $val;
                }
            }
        }

        // Inject P&L Net Profit into Equity / Liabilities
        $fy = FinancialYear::where('company_id', $company->id)->where('is_active', true)->first();
        $startDate = $fy ? $fy->start_date->format('Y-m-d') : date('Y-04-01');
        $pnl = $this->getProfitAndLoss($company, $startDate, $asOfDate);
        $netProfit = $pnl['net_profit'];

        $totalLiabilitiesWithProfit = $totalLiabilities + $netProfit;

        return [
            'assets' => $assetRows,
            'liabilities' => $liabilityRows,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_profit' => $netProfit,
            'total_liabilities_with_profit' => $totalLiabilitiesWithProfit,
            'is_balanced' => round($totalAssets, 2) === round($totalLiabilitiesWithProfit, 2),
            'difference' => abs(round($totalAssets - $totalLiabilitiesWithProfit, 2)),
        ];
    }

    /**
     * Dashboard financial summary cards and chart metrics.
     */
    public function getDashboardMetrics(Company $company): array
    {
        $salesTotal = (float) SalesInvoice::where('company_id', $company->id)->where('status', 'active')->sum('grand_total');
        $purchaseTotal = (float) PurchaseInvoice::where('company_id', $company->id)->where('status', 'active')->sum('grand_total');

        $receivables = (float) SalesInvoice::where('company_id', $company->id)->where('status', 'active')->sum('due_amount');
        $payables = (float) PurchaseInvoice::where('company_id', $company->id)->where('status', 'active')->sum('due_amount');

        $cashBalance = (float) Ledger::where('company_id', $company->id)->where('party_type', PartyType::CASH)->sum('current_balance');
        $bankBalance = (float) Ledger::where('company_id', $company->id)->where('party_type', PartyType::BANK)->sum('current_balance');

        $recentVouchers = Voucher::with(['partyLedger', 'creator'])
            ->where('company_id', $company->id)
            ->latest('voucher_date')
            ->latest('id')
            ->take(8)
            ->get();

        $topProducts = Product::where('company_id', $company->id)
            ->where('item_type', 'goods')
            ->orderBy('current_stock', 'desc')
            ->take(5)
            ->get();

        return [
            'sales_total' => $salesTotal,
            'purchase_total' => $purchaseTotal,
            'receivables' => $receivables,
            'payables' => $payables,
            'cash_balance' => $cashBalance,
            'bank_balance' => $bankBalance,
            'recent_vouchers' => $recentVouchers,
            'top_products' => $topProducts,
        ];
    }
}
