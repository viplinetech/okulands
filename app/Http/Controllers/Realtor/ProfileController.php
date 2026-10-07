<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Services\ImageProcessor;
use App\Support\Audit;
use App\Support\Banks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('realtor.profile', ['user' => $request->user(), 'banks' => Banks::LIST]);
    }

    public function update(Request $request, ImageProcessor $images): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s()\-]{7,20}$/'],
            'bank_name' => ['nullable', 'required_with:account_number,account_name', Rule::in(Banks::LIST)],
            'account_number' => ['nullable', 'required_with:bank_name,account_name', 'digits:10'],
            'account_name' => ['nullable', 'required_with:bank_name,account_number', 'string', 'max:120'],
            'avatar' => ['nullable', 'file', 'max:12288'],
        ], [
            'phone.regex' => 'Enter a valid phone number.',
            'account_number.digits' => 'A Nigerian account number has exactly 10 digits.',
            'bank_name.in' => 'Please choose your bank from the list.',
        ]);

        $bankChanged = ($data['bank_name'] ?? null) !== $user->bank_name
            || ($data['account_number'] ?? null) !== $user->account_number;

        $user->fill(collect($data)->only(['name', 'phone', 'bank_name', 'account_number', 'account_name'])->all());

        if ($request->hasFile('avatar')) {
            try {
                $old = $user->avatar;
                $user->avatar = $images->store($request->file('avatar'), 'avatars', 600, 82);
                $images->delete($old);
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['avatar' => $e->getMessage()]);
            }
        }

        $user->save();

        if ($bankChanged) {
            // The account number itself is never written to the log.
            Audit::log('profile.bank_updated', $user, 'Payout details changed');
        }

        return redirect()->route('realtor.profile')->with('success', 'Your profile has been saved.');
    }
}
