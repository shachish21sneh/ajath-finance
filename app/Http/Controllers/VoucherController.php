<?php

namespace App\Http\Controllers;

use App\Enums\VoucherType;
use App\Helpers\AccountingHelper;
use App\Models\ActivityLog;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function __construct(protected AccountingService $accountingService) {}

    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $query = Voucher::with(['partyLedger', 'creator'])->where('company_id', $company->id);

        if ($request->filled('type')) {
            $query->where('voucher_type', $request->type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('voucher_date', [$request->from_date, $request->to_date]);
        }

        $vouchers = $query->latest('voucher_date')->latest('id')->paginate(20);

        return view('vouchers.index', compact('vouchers', 'company'));
    }

    public function create(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $selectedType = $request->get('type', 'JOURNAL');
        if (!in_array($selectedType, array_column(VoucherType::cases(), 'value'))) {
            $selectedType = 'JOURNAL';
        }

        $voucherType = VoucherType::from($selectedType);
        $voucherNo = $this->accountingService->getNextVoucherNumber($company, $fy, $voucherType);
        $ledgers = Ledger::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();

        return view('vouchers.create', compact('voucherType', 'voucherNo', 'ledgers', 'company', 'fy'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = AccountingHelper::getActiveCompany();
        $fy = AccountingHelper::getActiveFinancialYear();

        $data = $request->validate([
            'voucher_type' => ['required', 'string'],
            'voucher_no' => ['required', 'string', 'max:50'],
            'voucher_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'party_ledger_id' => ['nullable', 'exists:ledgers,id'],
            'payment_mode' => ['required', 'in:cash,bank,cheque,upi,credit'],
            'cheque_no' => ['nullable', 'string', 'max:50'],
            'cheque_date' => ['nullable', 'date'],
            'narration' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:2'],
            'items.*.ledger_id' => ['required', 'exists:ledgers,id'],
            'items.*.entry_type' => ['required', 'in:debit,credit'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.narration' => ['nullable', 'string'],
        ]);

        $voucherData = [
            'company_id' => $company->id,
            'financial_year_id' => $fy->id,
            'voucher_type' => VoucherType::from($data['voucher_type']),
            'voucher_no' => $data['voucher_no'],
            'voucher_date' => $data['voucher_date'],
            'reference_no' => $data['reference_no'] ?? null,
            'party_ledger_id' => $data['party_ledger_id'] ?? null,
            'payment_mode' => $data['payment_mode'],
            'cheque_no' => $data['cheque_no'] ?? null,
            'cheque_date' => $data['cheque_date'] ?? null,
            'narration' => $data['narration'] ?? null,
            'status' => 'posted',
            'created_by' => auth()->id(),
        ];

        try {
            $voucher = $this->accountingService->createVoucher($voucherData, $data['items']);

            ActivityLog::log(
                "Post {$voucher->voucher_type->value}",
                'Accounting',
                "Posted voucher #{$voucher->voucher_no} for amount ₹ " . number_format($voucher->total_amount, 2)
            );

            return redirect()->route('vouchers.show', $voucher->id)
                ->with('success', "Voucher '{$voucher->voucher_no}' posted and balanced successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Voucher $voucher): View
    {
        $voucher->load(['items.ledger', 'partyLedger', 'creator', 'company', 'financialYear']);
        return view('vouchers.show', compact('voucher'));
    }
}
