<x-layouts.realtor title="Booked inspections">
    <x-app.page-header title="Booked inspections" kicker="Your referrals" subtitle="Whenever someone who came through your link books an inspection, it appears here. Oku Lands follows up with them directly, and you earn your commission when the sale completes." />

    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-brand/25 bg-brand/10 px-4 py-3.5 text-sm leading-relaxed text-ink">
        <x-icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-brand" />
        <span><strong>Client privacy:</strong> for their protection, the client&rsquo;s name and contact details are kept by Oku Lands. You can always see that a booking came from your link, which property it is for, and how it is progressing.</span>
    </div>

    {{-- Kind filter --}}
    <div class="tabs mb-5">
        <a href="{{ route('realtor.leads') }}" class="tab {{ ! $kind ? 'is-active' : '' }}">All <span class="opacity-60">{{ $total }}</span></a>
        @foreach ($kinds as $key => $label)
            <a href="{{ route('realtor.leads', ['kind' => $key]) }}" class="tab {{ $kind === $key ? 'is-active' : '' }}">{{ $label }} <span class="opacity-60">{{ $counts[$key] }}</span></a>
        @endforeach
    </div>

    <div class="card-flush">
        @if ($bookings->count())
            <table class="tbl">
                <thead><tr><th>Booking</th><th>Property</th><th>Status</th><th>Received</th></tr></thead>
                <tbody>
                @foreach ($bookings as $booking)
                    @php $inspection = $booking->type === 'inspection'; @endphp
                    <tr>
                        <td class="cell-main">
                            <div class="flex items-center gap-3 text-left">
                                <span class="stat-icon !h-10 !w-10"><x-icon :name="$inspection ? 'key' : 'mail'" class="h-4 w-4" /></span>
                                <div class="min-w-0">
                                    <p class="font-bold text-ink">{{ $inspection ? 'Inspection booked' : 'New enquiry' }}</p>
                                    <p class="text-xs font-normal text-mute">Someone from your link &middot; Ref #{{ $booking->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Property" class="max-w-[18rem]">
                            @if ($booking->property)
                                <a href="{{ route('properties.show', $booking->property->slug) }}" target="_blank" rel="noopener" class="line-clamp-2 font-semibold text-ink hover:text-brand">{{ $booking->property->title }}</a>
                            @else
                                <span class="text-mute">{{ $inspection ? 'General inspection' : 'General enquiry' }}</span>
                            @endif
                        </td>
                        <td data-label="Status"><x-app.badge :status="$booking->status" /></td>
                        <td data-label="Received" class="whitespace-nowrap text-mute">{{ $booking->created_at->format('M j, g:ia') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="inbox" :title="$kind === 'enquiries' ? 'No other enquiries yet' : 'No booked inspections yet'" text="Share your referral link on WhatsApp, Facebook and Instagram. When someone uses it to book an inspection, it appears here.">
                <a href="{{ route('realtor.share') }}" class="btn btn-primary btn-sm">Share your link</a>
            </x-app.empty>
        @endif
    </div>

    <div class="mt-6">{{ $bookings->links('pagination.premium') }}</div>
</x-layouts.realtor>
