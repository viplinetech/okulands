<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Support\Qr;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $link = $user->referralLink();
        $inviteLink = $user->referralLink('/register');

        $message = "Looking for verified land or property in Nigeria? Oku Lands & Properties documents every listing and offers guided inspections. Take a look: {$link}";

        return view('realtor.share', [
            'link' => $link,
            'inviteLink' => $inviteLink,
            'message' => $message,
            'qr' => Qr::svg($link, 220),
            'clicks7' => $user->referralVisits()->where('created_at', '>=', now()->subDays(7))->count(),
            'clicks30' => $user->referralVisits()->where('created_at', '>=', now()->subDays(30))->count(),
            'clicksAll' => $user->referralVisits()->count(),
        ]);
    }
}
