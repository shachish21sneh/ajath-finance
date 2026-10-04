<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\BatteryWarranty;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\SolarProject;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        return view('ai-assistant.index', compact('company'));
    }

    public function query(Request $request): JsonResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $query = strtolower(trim($request->input('query', '')));

        if (empty($query)) {
            return response()->json(['reply' => 'Please ask a business query (in English or Hindi).', 'type' => 'info']);
        }

        // 1. Today's sales / आज की बिक्री
        if (str_contains($query, 'sale') || str_contains($query, 'बिक्री') || str_contains($query, 'sales')) {
            $todaySales = SalesInvoice::where('company_id', $company->id)
                ->where('invoice_date', now()->toDateString())
                ->where('status', 'active')
                ->sum('grand_total');

            $allSales = SalesInvoice::where('company_id', $company->id)
                ->where('status', 'active')
                ->sum('grand_total');

            $count = SalesInvoice::where('company_id', $company->id)
                ->where('status', 'active')
                ->count();

            $reply = "📊 **Sales Intelligence Summary:**\n" .
                "• Today's Sales: **₹ " . number_format($todaySales, 2) . "**\n" .
                "• Total Cumulative Revenue: **₹ " . number_format($allSales, 2) . "** across {$count} invoices.\n" .
                "All sales transactions are backed by double-entry ledger vouchers and automated GST compliance.";

            return response()->json(['reply' => $reply, 'type' => 'success']);
        }

        // 2. Overdue payments / किस customer का payment overdue है
        if (str_contains($query, 'overdue') || str_contains($query, 'customer') || str_contains($query, 'बकाया') || str_contains($query, 'receivable')) {
            $overdueInvoices = SalesInvoice::with('customer')
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->where('due_amount', '>', 0)
                ->orderBy('due_amount', 'desc')
                ->take(5)
                ->get();

            if ($overdueInvoices->isEmpty()) {
                return response()->json(['reply' => '✅ Good news! There are currently no customer overdue payments.', 'type' => 'success']);
            }

            $list = [];
            foreach ($overdueInvoices as $inv) {
                $cust = $inv->customer?->name ?? 'Unknown Customer';
                $list[] = "• **{$cust}** (Inv #{$inv->invoice_no}): Due **₹ " . number_format($inv->due_amount, 2) . "** (Due date: " . ($inv->due_date ? date('d M Y', strtotime($inv->due_date)) : 'Immediate') . ")";
            }

            $reply = "⚠️ **Customer Receivables & Overdue Accounts:**\n" . implode("\n", $list);
            return response()->json(['reply' => $reply, 'type' => 'warning']);
        }

        // 3. Gross profit / लाभ / profit
        if (str_contains($query, 'profit') || str_contains($query, 'लाभ') || str_contains($query, 'gross')) {
            $fy = AccountingHelper::getActiveFinancialYear();
            $startDate = $fy ? $fy->start_date->format('Y-m-d') : date('Y-04-01');
            $pnl = $this->reportService->getProfitAndLoss($company, $startDate, date('Y-m-d'));

            $gross = $pnl['gross_profit'];
            $net = $pnl['net_profit'];

            $reply = "📈 **Profitability Analysis (FY " . ($fy?->title ?? 'Current') . "):**\n" .
                "• Total Direct Incomes: **₹ " . number_format($pnl['total_direct_income'], 2) . "**\n" .
                "• Cost of Goods / Direct Expenses: **₹ " . number_format($pnl['total_direct_expense'], 2) . "**\n" .
                "• **Gross Profit:** **₹ " . number_format($gross, 2) . "**\n" .
                "• Operating / Indirect Expenses: **₹ " . number_format($pnl['total_indirect_expense'], 2) . "**\n" .
                "• **Net Profit:** **₹ " . number_format($net, 2) . "**";

            return response()->json(['reply' => $reply, 'type' => 'success']);
        }

        // 4. Low stock / कौन सा product low stock में है
        if (str_contains($query, 'stock') || str_contains($query, 'low') || str_contains($query, 'स्टॉक')) {
            $lowStock = Product::where('company_id', $company->id)
                ->where('item_type', 'goods')
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->take(5)
                ->get();

            if ($lowStock->isEmpty()) {
                return response()->json(['reply' => '✅ All products have adequate inventory levels above reorder thresholds.', 'type' => 'success']);
            }

            $items = [];
            foreach ($lowStock as $p) {
                $items[] = "• **{$p->name}**: Current Stock: **{$p->current_stock}** (Reorder threshold: {$p->reorder_level})";
            }

            $reply = "🚨 **Low Stock Alert — Reorder Required:**\n" . implode("\n", $items);
            return response()->json(['reply' => $reply, 'type' => 'danger']);
        }

        // 5. Battery warranty / वारंटी
        if (str_contains($query, 'battery') || str_contains($query, 'warranty') || str_contains($query, 'वारंटी') || str_contains($query, 'बैटरी')) {
            $expiring = BatteryWarranty::with(['batterySerial.model.product', 'customer'])
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->whereBetween('warranty_end_date', [now(), now()->addDays(60)])
                ->take(5)
                ->get();

            $totalActive = BatteryWarranty::where('company_id', $company->id)->where('is_active', true)->count();

            $reply = "🔋 **Battery Warranty Status:**\n" .
                "• Total Active Registered Warranties: **{$totalActive}**\n";

            if ($expiring->isNotEmpty()) {
                $reply .= "• Expiring within 60 days:\n";
                foreach ($expiring as $w) {
                    $serial = $w->batterySerial?->serial_number ?? 'N/A';
                    $prod = $w->batterySerial?->model?->product?->name ?? 'Battery';
                    $reply .= "  - Serial: **{$serial}** ({$prod}) expiring on " . date('d M Y', strtotime($w->warranty_end_date)) . "\n";
                }
            } else {
                $reply .= "• No battery warranties expiring in the next 60 days.";
            }

            return response()->json(['reply' => $reply, 'type' => 'info']);
        }

        // 6. Dealer outstanding / डीलर
        if (str_contains($query, 'dealer') || str_contains($query, 'डीलर') || str_contains($query, 'distributor')) {
            $dealers = Dealer::where('company_id', $company->id)
                ->orderBy('outstanding_balance', 'desc')
                ->take(5)
                ->get();

            if ($dealers->isEmpty()) {
                return response()->json(['reply' => 'No dealer accounts found.', 'type' => 'info']);
            }

            $top = $dealers->first();
            $reply = "🤝 **Dealer & Distributor Portfolio:**\n" .
                "• Highest Outstanding: **{$top->name}** ({$top->dealer_code}) with **₹ " . number_format($top->outstanding_balance, 2) . "** outstanding (Credit Limit: ₹ " . number_format($top->credit_limit, 2) . ").\n" .
                "• Price Tier: {$top->price_tier} | Territory: {$top->territory}";

            return response()->json(['reply' => $reply, 'type' => 'info']);
        }

        // 7. Solar projects / सोलर
        if (str_contains($query, 'solar') || str_contains($query, 'सोलर') || str_contains($query, 'installation')) {
            $projects = SolarProject::where('company_id', $company->id)->get();
            $totalKw = (float) $projects->sum('capacity_kw');
            $commissioned = $projects->where('installation_status', 'COMMISSIONED')->count();
            $inProgress = $projects->count() - $commissioned;

            $reply = "☀️ **Solar EPC Projects Summary:**\n" .
                "• Total Projects: **{$projects->count()}**\n" .
                "• Total Aggregated Capacity: **{$totalKw} kW**\n" .
                "• Commissioned: **{$commissioned}** | In-Progress Installations: **{$inProgress}**\n" .
                "• All projects are integrated with Rooftop Site Surveys and Net Metering workflows.";

            return response()->json(['reply' => $reply, 'type' => 'success']);
        }

        // Fallback natural response
        return response()->json([
            'reply' => "🤖 I analyzed your query for **{$company->name}**:\n" .
                "You can ask me questions like:\n" .
                "• *'What is today\'s total sales?' / 'आज की बिक्री कितनी है?'*\n" .
                "• *'Which customer payment is overdue?' / 'किस customer का payment overdue है?'*\n" .
                "• *'What is the gross profit this month?'*\n" .
                "• *'Which products are in low stock?'*\n" .
                "• *'What is the battery warranty expiry status?'*\n" .
                "• *'How many solar installations are active?'*",
            'type' => 'info'
        ]);
    }
}
