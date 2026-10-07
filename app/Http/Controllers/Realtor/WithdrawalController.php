<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalRequested;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! filled($user->bank_name) || ! filled($user->account_number) || ! filled($user->account_name)) {
            return back()->with('error', 'Add your bank details before requesting a withdrawal.');
        }

        $available = $user->availableBalance();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.max(1, $available)],
        ], [
            'amount.max' => 'You can request up to your available balance of ₦'.number_format($available).'.',
        ]);

        if ($available <= 0) {
            return back()->with('error', 'You have no available balance to withdraw right now.');
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'status' => 'pending',
            'bank_name' => $user->bank_name,
            'account_name' => $user->account_name,
            'account_number' => $user->account_number,
        ]);

        Audit::log('withdrawal.requested', $withdrawal, $user->name.' requested a withdrawal of ₦'.number_format((float) $withdrawal->amount), [], $user->id);

        try {
            User::where('role', 'admin')->where('status', 'active')->get()->each->notify(new WithdrawalRequested($withdrawal));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Withdrawal requested. We will process it shortly.');
    }
}
