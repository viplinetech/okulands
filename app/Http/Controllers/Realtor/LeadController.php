<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * "Booked inspections": what came in through the realtor's own referral link.
 *
 * PRIVACY: the realtor is told THAT someone booked, for WHICH property and WHEN, but never WHO. The client's name,
 * phone, email and message stay with Oku Lands (the admin follows up), so referrals cannot be worked around.
 */
class LeadController extends Controller
{
    public const KINDS = ['inspections' => 'Booked inspections', 'enquiries' => 'Other enquiries'];

    public function index(Request $request)
    {
        $user = $request->user();
        $kind = array_key_exists($request->query('kind'), self::KINDS) ? $request->query('kind') : null;

        $counts = [
            'inspections' => $user->referredBookings()->where('type', 'inspection')->count(),
            'enquiries' => $user->referredBookings()->where('type', '!=', 'inspection')->count(),
        ];

        $bookings = $user->referredBookings()
            ->with('property:id,title,slug')
            ->when($kind === 'inspections', fn ($q) => $q->where('type', 'inspection'))
            ->when($kind === 'enquiries', fn ($q) => $q->where('type', '!=', 'inspection'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('realtor.leads', [
            'bookings' => $bookings,
            'kind' => $kind,
            'kinds' => self::KINDS,
            'counts' => $counts,
            'total' => array_sum($counts),
        ]);
    }
}
