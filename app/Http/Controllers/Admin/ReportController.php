<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Exports\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public const PERIODS = ['30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last 12 months', 'all' => 'All time'];

    private function since(Request $request): ?\Illuminate\Support\Carbon
    {
        $period = array_key_exists((string) $request->query('period'), self::PERIODS) ? (string) $request->query('period') : '90';

        return $period === 'all' ? null : now()->subDays((int) $period)->startOfDay();
    }

    public function index(Request $request)
    {
        $period = array_key_exists((string) $request->query('period'), self::PERIODS) ? (string) $request->query('period') : '90';
        $since = $this->since($request);
        $counted = ['approved', 'paid'];

        $sales = Sale::whereIn('status', $counted)->when($since, fn ($q) => $q->where('created_at', '>=', $since));

        $months = Sale::whereIn('status', $counted)->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->get(['amount', 'created_at'])
            ->groupBy(fn (Sale $s) => $s->created_at->format('Y-m'))
            ->sortKeys()
            ->map(fn ($rows, $key) => ['label' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $key)->format('M y'), 'value' => (float) $rows->sum('amount')])
            ->values()->take(-12)->all();

        $performance = User::affiliates()
            ->withCount([
                'referralVisits as clicks',
                'referredLeads as leads' => fn ($q) => $q->when($since, fn ($w) => $w->where('created_at', '>=', $since)),
                'sales as closed' => fn ($q) => $q->whereIn('status', $counted)->when($since, fn ($w) => $w->where('created_at', '>=', $since)),
            ])
            ->withSum(['sales as revenue' => fn ($q) => $q->whereIn('status', $counted)->when($since, fn ($w) => $w->where('created_at', '>=', $since))], 'amount')
            ->withSum(['commissions as earned' => fn ($q) => $q->when($since, fn ($w) => $w->where('created_at', '>=', $since))], 'amount')
            ->orderByDesc('revenue')
            ->take(15)
            ->get();

        return view('admin.reports', [
            'period' => $period,
            'periods' => self::PERIODS,
            'totals' => [
                'revenue' => (float) (clone $sales)->sum('amount'),
                'sales' => (clone $sales)->count(),
                'leads' => Lead::when($since, fn ($q) => $q->where('created_at', '>=', $since))->count(),
                'commission' => (float) Commission::when($since, fn ($q) => $q->where('created_at', '>=', $since))->sum('amount'),
                'paid' => (float) Commission::where('status', 'paid')->when($since, fn ($q) => $q->where('created_at', '>=', $since))->sum('amount'),
            ],
            'months' => $months,
            'performance' => $performance,
        ]);
    }

    /** Excel downloads: realtors, sales, commissions, leads. */
    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['realtors', 'sales', 'commissions', 'leads'], true), 404);
        $since = $this->since($request);
        $date = fn ($d) => $d?->format('Y-m-d H:i');

        [$headings, $rows] = match ($type) {
            'realtors' => [
                ['Name', 'Email', 'Phone', 'Referral code', 'Status', 'Sponsor', 'Joined', 'Leads', 'Sales closed', 'Total commission (₦)'],
                User::affiliates()->with('referrer:id,name')->withCount(['referredLeads as leads', 'sales as closed' => fn ($q) => $q->whereIn('status', ['approved', 'paid'])])->withSum('commissions as earned', 'amount')->orderBy('name')->get()
                    ->map(fn (User $u) => [$u->name, $u->email, $u->phone, $u->referral_code, $u->status, $u->referrer?->name, $date($u->created_at), $u->leads, $u->closed, (float) $u->earned])->all(),
            ],
            'sales' => [
                ['Date', 'Reference', 'Property', 'Buyer', 'Realtor', 'Amount (₦)', 'Status'],
                Sale::with(['property:id,title', 'realtor:id,name'])->when($since, fn ($q) => $q->where('created_at', '>=', $since))->latest()->get()
                    ->map(fn (Sale $s) => [$date($s->created_at), $s->reference, $s->property?->title, $s->buyer_name, $s->realtor?->name, (float) $s->amount, $s->status])->all(),
            ],
            'commissions' => [
                ['Date', 'Realtor', 'Bank', 'Account name', 'Account number', 'Property', 'Tier', 'Rate %', 'Amount (₦)', 'Status', 'Paid on', 'Reference'],
                Commission::with(['user', 'sale.property:id,title'])->when($since, fn ($q) => $q->where('created_at', '>=', $since))->latest()->get()
                    ->map(fn (Commission $c) => [$date($c->created_at), $c->user?->name, $c->user?->bank_name, $c->user?->account_name, $c->user?->account_number, $c->sale?->property?->title, $c->tier, (float) $c->rate, (float) $c->amount, $c->status, $date($c->paid_at), $c->payout_reference])->all(),
            ],
            'leads' => [
                ['Date', 'Name', 'Email', 'Phone', 'Type', 'Property', 'Referred by', 'Status', 'Message'],
                Lead::with(['property:id,title', 'referrer:id,name'])->when($since, fn ($q) => $q->where('created_at', '>=', $since))->latest()->get()
                    ->map(fn (Lead $l) => [$date($l->created_at), $l->name, $l->email, $l->phone, $l->type, $l->property?->title, $l->referrer?->name, $l->status, $l->message])->all(),
            ],
        };

        Audit::log('report.exported', null, "Exported {$type} report", ['rows' => count($rows)]);

        return Excel::download(new TableExport($headings, $rows), "oku-lands-{$type}-".now()->format('Y-m-d').'.xlsx');
    }
}
