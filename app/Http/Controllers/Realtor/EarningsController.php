<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Services\RealtorStats;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = in_array($request->query('status'), ['pending', 'paid'], true) ? $request->query('status') : null;

        $commissions = $user->commissions()
            ->with('sale.property:id,title')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('realtor.earnings', [
            'commissions' => $commissions,
            'status' => $status,
            'summary' => (new RealtorStats($user))->summary(),
            'months' => (new RealtorStats($user))->monthlyEarnings(),
            'hasBank' => filled($user->bank_name) && filled($user->account_number),
            'available' => $user->availableBalance(),
            'withdrawals' => $user->withdrawals()->latest()->take(10)->get(),
        ]);
    }
}
