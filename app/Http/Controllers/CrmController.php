<?php

namespace App\Http\Controllers;

use App\Helpers\AccountingHelper;
use App\Models\CrmLead;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmController extends Controller
{
    public function index(Request $request): View
    {
        $company = AccountingHelper::getActiveCompany();
        $allLeads = CrmLead::where('company_id', $company->id)->latest()->get();
        $leads = CrmLead::where('company_id', $company->id)->latest()->paginate(15);

        $pipeline = [
            'NEW' => $allLeads->where('stage', 'NEW'),
            'CONTACTED' => $allLeads->where('stage', 'CONTACTED'),
            'PROPOSAL' => $allLeads->where('stage', 'PROPOSAL'),
            'NEGOTIATION' => $allLeads->where('stage', 'NEGOTIATION'),
            'WON' => $allLeads->where('stage', 'WON'),
            'LOST' => $allLeads->where('stage', 'LOST'),
        ];

        return view('crm.index', compact('company', 'leads', 'allLeads', 'pipeline'));
    }

    public function store(Request $request)
    {
        $company = AccountingHelper::getActiveCompany();
        $validated = $request->validate([
            'contact_name' => 'required|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email',
            'source' => 'nullable|string',
            'product_interest' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|string',
            'stage' => 'required|in:NEW,CONTACTED,PROPOSAL,NEGOTIATION,WON,LOST',
            'next_follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['estimated_value'] = $validated['estimated_value'] ?? 0;

        CrmLead::create($validated);

        return redirect()->route('crm.index')->with('success', "CRM Lead '{$validated['contact_name']}' created successfully.");
    }

    public function update(Request $request, CrmLead $lead)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email',
            'source' => 'nullable|string',
            'product_interest' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|string',
            'stage' => 'required|in:NEW,CONTACTED,PROPOSAL,NEGOTIATION,WON,LOST',
            'next_follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('crm.index')->with('success', "CRM Lead '{$lead->contact_name}' updated successfully.");
    }

    public function updateStage(Request $request, CrmLead $lead)
    {
        $validated = $request->validate([
            'stage' => 'required|in:NEW,CONTACTED,PROPOSAL,NEGOTIATION,WON,LOST',
        ]);

        $lead->update($validated);

        return redirect()->route('crm.index')->with('success', "Lead stage updated to {$validated['stage']}.");
    }
}
