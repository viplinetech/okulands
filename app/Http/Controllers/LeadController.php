<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'type' => ['nullable', 'in:inspection,partnership,general'],
        ]);

        Lead::create([
            ...$data,
            'type' => $data['type'] ?? 'general',
            'source' => 'website_contact_form',
        ]);

        return back()->with('status', 'Thank you! Your message has been received, our team will reach out shortly.');
    }
}
