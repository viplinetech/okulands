<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ReferralVisit;
use App\Models\Service;
use App\Models\User;
use App\Notifications\InspectionBooked;
use App\Notifications\NewLeadAlert;
use App\Notifications\NewReferralLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LeadController extends Controller
{
    /**
     * Contact-form interest options: the fixed choices plus one per ACTIVE service, so a hidden service
     * never appears on the form. Each maps to the lead type stored for the admin.
     */
    public static function interests(): array
    {
        $options = [
            'buying' => ['Buying land or property', 'general'],
            'inspection' => ['Booking a site inspection', 'inspection'],
        ];

        foreach (Service::visible()->get(['id', 'title']) as $service) {
            $options['service-'.$service->id] = [$service->title, 'general'];
        }

        return $options + [
            'realtor' => ['Becoming a realtor', 'partnership'],
            'other' => ['Something else', 'general'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $thanks = 'Thank you! Your message has been received. Our team will reach out shortly.';
        $back = url()->previous(url('/contact')).'#enquire';

        // Honeypot: real visitors never see or fill this field; bots do.
        if ($request->filled('website')) {
            return redirect($back)->with('status', $thanks);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_code' => ['nullable', 'string', 'max:6'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{6,15}$/'],
            'message' => ['nullable', 'string', 'max:2000'],
            'interest' => ['nullable', 'in:'.implode(',', array_keys(self::interests()))],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ], [
            'phone.required' => 'Please enter your phone number so we can reach you.',
            'phone.regex' => 'Enter the phone number in digits only, without the country code.',
        ]);

        // One international number is stored, e.g. +2348012345678, so every lead can be called or messaged.
        $data['phone'] = \App\Support\Countries::combine($data['phone_code'] ?? null, $data['phone']);

        $interest = $data['interest'] ?? null;
        $options = self::interests();
        $type = ! empty($data['property_id']) ? 'inspection' : ($options[$interest][1] ?? 'general');
        $message = trim(($interest ? '['.$options[$interest][0].'] ' : '').($data['message'] ?? ''));

        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'message' => $message ?: null,
            'type' => $type,
            'property_id' => $data['property_id'] ?? null,
            'source' => ! empty($data['property_id']) ? 'website_property_page' : 'website_contact_form',
            // Referral protection: credit the realtor this visitor first arrived through.
            'referrer_id' => $this->referrerFor($request),
        ]);

        $this->alert($lead);

        return redirect($back)->with('status', $thanks);
    }

    /**
     * Alerts for a new enquiry or inspection booking: the referring realtor's dashboard + email,
     * every active admin's dashboard + email, and (for a booked inspection) a confirmation email
     * back to the visitor who booked it. A failure here never blocks the enquiry itself.
     */
    private function alert(Lead $lead): void
    {
        try {
            $lead->referrer?->notify(new NewReferralLead($lead));
            User::where('role', 'admin')->where('status', 'active')->get()->each->notify(new NewLeadAlert($lead));

            if ($lead->type === 'inspection' && $lead->email) {
                Notification::route('mail', $lead->email)->notify(new InspectionBooked($lead));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function referrerFor(Request $request): ?int
    {
        $token = $request->cookie(ReferralController::COOKIE);

        return $token ? ReferralVisit::where('visitor_token', $token)->value('referrer_id') : null;
    }
}
