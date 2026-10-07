<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $filter = in_array($request->query('show'), ['pending', 'approved'], true) ? $request->query('show') : null;

        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::query()
                ->when($filter === 'pending', fn ($q) => $q->where('approved', false))
                ->when($filter === 'approved', fn ($q) => $q->where('approved', true))
                ->latest()->paginate(15)->withQueryString(),
            'filter' => $filter,
            'pending' => Testimonial::where('approved', false)->count(),
            'approved' => Testimonial::where('approved', true)->count(),
        ]);
    }

    /** Add a review manually (already approved), e.g. one received by phone or in person. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:120'],
            'client_role' => ['nullable', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'min:10', 'max:800'],
        ]);

        $t = new Testimonial($data);
        $t->approved = true;
        $t->save();

        Audit::log('testimonial.created', $t, 'Added review from '.$t->client_name);

        return back()->with('success', 'Review added and published.');
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $request->validate(['approved' => ['required', 'boolean']]);

        $testimonial->forceFill(['approved' => (bool) $data['approved']])->save();
        Audit::log($testimonial->approved ? 'testimonial.approved' : 'testimonial.unapproved', $testimonial, 'Review from '.$testimonial->client_name);

        return back()->with('success', $testimonial->approved ? 'Review published on the website.' : 'Review hidden from the website.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Audit::log('testimonial.deleted', null, 'Deleted review from '.$testimonial->client_name);
        $testimonial->delete();

        return back()->with('success', 'Review deleted.');
    }
}
