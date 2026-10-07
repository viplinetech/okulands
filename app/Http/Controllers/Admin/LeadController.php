<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public const STATUSES = ['new', 'contacted', 'hot', 'converted', 'closed'];

    public function index(Request $request)
    {
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $term = trim((string) $request->query('q'));

        $leads = Lead::query()
            ->with(['property:id,title', 'referrer:id,name'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->query('source') === 'referred', fn ($q) => $q->whereNotNull('referrer_id'))
            ->when($request->query('source') === 'direct', fn ($q) => $q->whereNull('referrer_id'))
            ->when($term, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'status' => $status,
            'term' => $term,
            'counts' => Lead::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(Lead $lead)
    {
        return view('admin.leads.show', [
            'lead' => $lead->load(['property', 'referrer']),
            'realtors' => User::affiliates()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'notes' => ['nullable', 'string', 'max:3000'],
            'referrer_id' => ['nullable', Rule::exists('users', 'id')->whereIn('role', User::AFFILIATE_ROLES)],
        ]);

        $before = $lead->only(['status', 'referrer_id']);
        $lead->fill($data);
        if ($lead->isDirty('status') && $lead->status === 'contacted' && ! $lead->contacted_at) {
            $lead->contacted_at = now();
        }
        $lead->save();

        Audit::log('lead.updated', $lead, 'Lead '.$lead->name.' updated', ['from' => $before, 'to' => $lead->only(['status', 'referrer_id'])]);

        return back()->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Audit::log('lead.deleted', null, 'Deleted lead '.$lead->name);
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
