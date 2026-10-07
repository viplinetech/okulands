<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Services\RealtorStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $stats = new RealtorStats($user);

        return view('realtor.dashboard', [
            'summary' => $stats->summary(),
            'months' => $stats->monthlyEarnings(),
            'checklist' => $stats->checklist(),
            'recentLeads' => $user->referredBookings()->with('property:id,title,slug')->latest()->take(4)->get(),
            'recentCommissions' => $user->commissions()->with('sale.property')->latest()->take(4)->get(),
        ]);
    }
}
