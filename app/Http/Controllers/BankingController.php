<?php

namespace App\Http\Controllers;

use App\Enums\PartyType;
use App\Helpers\AccountingHelper;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankingController extends Controller
{
    public function index(): View
    {
        $company = AccountingHelper::getActiveCompany();
        $bankLedgers = Ledger::where('company_id', $company->id)->where('party_type', PartyType::BANK)->get();
        $cashLedgers = Ledger::where('company_id', $company->id)->where('party_type', PartyType::CASH)->get();

        $allBankCashIds = $bankLedgers->pluck('id')->merge($cashLedgers->pluck('id'));

        $recentEntries = LedgerEntry::with(['voucher', 'ledger'])
            ->whereIn('ledger_id', $allBankCashIds)
            ->latest('entry_date')
            ->latest('id')
            ->take(20)
            ->get();

        return view('banking.index', compact('bankLedgers', 'cashLedgers', 'recentEntries', 'company'));
    }
}
