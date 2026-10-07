<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request, TwoFactorService $twoFactor)
    {
        $user = $request->user();
        $secret = $request->session()->get('two_factor_pending_secret');

        return view('realtor.security', [
            'user' => $user,
            'pendingSecret' => $secret,
            'qr' => $secret ? $twoFactor->qrSvg($user->email, $secret) : null,
        ]);
    }
}
