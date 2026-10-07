<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The admin's daily starting point. It answers one question: what needs me today?
 * Reports, charts and rankings live on their own pages, so they don't crowd this one.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $monthStart = now()->startOfMonth();
        $counted = ['approved', 'paid'];

        return view('admin.dashboard', [
            // Things waiting for a decision or a follow-up, shown first.
            'todo' => [
                ['label' => 'New enquiries to follow up', 'count' => Lead::where('status', 'new')->count(), 'route' => 'admin.leads.index', 'query' => ['status' => 'new'], 'icon' => 'inbox'],
                ['label' => 'Sales to approve', 'count' => Sale::where('status', 'pending')->count(), 'route' => 'admin.sales.index', 'query' => ['status' => 'pending'], 'icon' => 'tag'],
                ['label' => 'Commissions to pay', 'count' => Commission::where('status', 'pending')->count(), 'route' => 'admin.commissions.index', 'query' => [], 'icon' => 'wallet'],
                ['label' => 'Client reviews to publish', 'count' => Testimonial::where('approved', false)->count(), 'route' => 'admin.testimonials.index', 'query' => ['show' => 'pending'], 'icon' => 'star'],
            ],
            // This month, in three numbers.
            'month' => [
                'sales' => (float) Sale::whereIn('status', $counted)->where('created_at', '>=', $monthStart)->sum('amount'),
                'enquiries' => Lead::where('created_at', '>=', $monthStart)->count(),
                'realtors' => User::affiliates()->where('status', 'active')->whereNotNull('email_verified_at')->count(),
            ],
            'latestLeads' => Lead::with(['property:id,title'])->latest()->take(5)->get(),
        ]);
    }
}
