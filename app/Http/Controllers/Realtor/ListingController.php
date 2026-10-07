<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Realtor;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

/** Available listings with a ready-made, personally tracked share link for each. */
class ListingController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'sector' => ['nullable', 'in:'.implode(',', array_keys(Property::SECTORS))],
        ]);

        $properties = Property::query()
            ->where('status', 'available')
            ->when($data['q'] ?? null, fn ($q, $t) => $q->where(fn ($w) => $w->where('title', 'like', "%{$t}%")->orWhere('location', 'like', "%{$t}%")))
            ->when($data['sector'] ?? null, fn ($q, $s) => $q->where('sector', $s))
            ->orderByDesc('featured')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('realtor.properties', ['properties' => $properties, 'filters' => $data, 'user' => $request->user()]);
    }
}
