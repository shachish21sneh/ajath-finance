<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\Ledger;
use App\Models\ServiceTicket;
use App\Models\ServiceVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $tickets = ServiceTicket::with(['customer', 'visits'])
            ->where('company_id', $company->id)
            ->latest('created_date')
            ->get();

        $visits = ServiceVisit::with('ticket')
            ->latest('visit_date')
            ->take(30)
            ->get();

        $customers = Ledger::where('company_id', $company->id)->where('party_type', 'customer')->get();

        return view('service.index', compact('company', 'tickets', 'visits', 'customers'));
    }

    public function storeTicket(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'ticket_no' => 'required|string|unique:service_tickets,ticket_no',
            'customer_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'product_name' => 'required|string|max:150',
            'serial_number' => 'nullable|string',
            'complaint_details' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'assigned_technician' => 'nullable|string',
            'created_date' => 'required|date',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = 'OPEN';

        ServiceTicket::create($validated);

        return redirect()->route('service.index')->with('success', "Service Ticket {$validated['ticket_no']} logged.");
    }

    public function storeVisit(Request $request)
    {
        $validated = $request->validate([
            'service_ticket_id' => 'required|exists:service_tickets,id',
            'visit_date' => 'required|date',
            'technician_name' => 'required|string',
            'findings' => 'required|string',
            'action_taken' => 'required|string',
            'parts_used' => 'nullable|string',
            'service_charges' => 'nullable|numeric|min:0',
            'status' => 'required|in:COMPLETED,PENDING_PARTS,ESCALATED',
        ]);

        $validated['service_charges'] = $validated['service_charges'] ?? 0;
        ServiceVisit::create($validated);

        $ticket = ServiceTicket::find($validated['service_ticket_id']);
        if ($validated['status'] === 'COMPLETED') {
            $ticket->update(['status' => 'RESOLVED', 'resolved_date' => $validated['visit_date']]);
        } else {
            $ticket->update(['status' => 'IN_PROGRESS']);
        }

        return redirect()->route('service.index')->with('success', "Technician Field Visit record saved for Ticket {$ticket->ticket_no}.");
    }
}
