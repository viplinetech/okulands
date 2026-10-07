<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyView;
use App\Notifications\HotLead;
use App\Services\ReferralAttribution;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public const BUDGETS = [
        '0-5000000' => 'Under ₦5M',
        '5000000-20000000' => '₦5M – ₦20M',
        '20000000-' => 'Above ₦20M',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'in:'.implode(',', array_keys(Property::SECTORS))],
            'status' => ['nullable', 'in:available,reserved,sold'],
            'budget' => ['nullable', 'in:'.implode(',', array_keys(self::BUDGETS))],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc'],
        ]);

        $query = Property::query()
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($w) => $w
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%")
                    ->orWhere('type', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->when($filters['sector'] ?? null, fn ($q, $v) => $q->where('sector', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['budget'] ?? null, function ($q, $range) {
                [$min, $max] = array_pad(explode('-', $range), 2, '');
                if ($min !== '') {
                    $q->where('price', '>=', (int) $min);
                }
                if ($max !== '') {
                    $q->where('price', '<=', (int) $max);
                }
            });

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            // Available listings first, then newest.
            default => $query->orderByRaw("status = 'available' desc")->orderByDesc('featured')->latest(),
        };

        return view('properties.index', [
            'properties' => $query->paginate(9)->withQueryString(),
            'filters' => $filters,
            'budgets' => self::BUDGETS,
            'total' => Property::count(),
            'locations' => Property::locations(),
        ]);
    }

    /**
     * "Hot lead" detection: a visitor tagged to a realtor who keeps coming back to the same property is
     * flagged to that realtor (once, on the 3rd view). Browser prefetch/prerender is not a real visit.
     */
    private function trackView(Request $request, Property $property): void
    {
        if (str_contains((string) $request->header('Sec-Purpose'), 'prefetch') || $request->header('Purpose') === 'prefetch') {
            return;
        }

        $visit = app(ReferralAttribution::class)->visit($request);
        if (! $visit) {
            return;
        }

        $view = PropertyView::firstOrCreate(
            ['visitor_token' => $visit->visitor_token, 'property_id' => $property->id],
            ['referrer_id' => $visit->referrer_id, 'views' => 0]
        );
        $view->increment('views');

        if ($view->views >= 3 && ! $view->alerted_at && $visit->referrer) {
            $visit->referrer->notify(new HotLead($property, $view->views));
            $view->update(['alerted_at' => now()]);
        }
    }

    public function show(Request $request, string $slug)
    {
        $property = Property::where('slug', $slug)->firstOrFail();

        $this->trackView($request, $property);

        $related = Property::query()
            ->where('id', '!=', $property->id)
            ->where('status', 'available')
            ->orderByRaw('sector = ? desc', [$property->sector])
            ->latest()
            ->take(3)
            ->get();

        return view('properties.show', compact('property', 'related'));
    }
}
