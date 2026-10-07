<x-layouts.standalone title="Welcome">
    @php
        $group = config('services.realtor_whatsapp_group');
        $first = auth()->user()->firstName();
        $code = auth()->user()->referral_code;
    @endphp

    <section class="mx-auto max-w-2xl">
        {{-- Success --}}
        <div class="card text-center !p-7 sm:!p-10">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 ring-8 ring-emerald-500/10">
                <x-icon name="check-circle" class="h-8 w-8" />
            </span>
            <p class="eyebrow mt-6 text-brand">Registration successful</p>
            <h1 class="display mt-3 text-4xl text-ink sm:text-5xl">Welcome to Oku Lands, {{ $first }}.</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-mute">Your email is confirmed and your account is ready. Your referral code is <strong class="font-mono text-ink">{{ $code }}</strong>, and it is already on your share link.</p>
        </div>

        {{-- Next steps --}}
        <div class="mt-5 space-y-3">
            @if ($group)
                <div class="rounded-3xl border border-brand/25 bg-brand/10 p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#25D366] text-white">
                            <x-icon name="whatsapp" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-bold text-ink">Join the Oku Lands Realtor WhatsApp Group</p>
                            <p class="mt-1 text-sm leading-relaxed text-ink/80">This is where new verified listings, sales wins, training and commission updates reach our realtors first. Realtors who are in the group close more deals, because they hear about opportunities before anyone else. <strong class="text-ink">Please join now.</strong></p>
                        </div>
                    </div>
                    <a href="{{ $group }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary mt-5 w-full sm:w-auto">
                        <x-icon name="whatsapp" class="h-4 w-4" /> Join the Realtor WhatsApp Group
                    </a>
                </div>
            @endif

            <div class="card !p-5 sm:!p-6">
                <p class="font-bold text-ink">Ready to start?</p>
                <p class="mt-1 text-sm leading-relaxed text-mute">Open your dashboard to share your link, track your leads and follow your earnings.</p>
                <a href="{{ route('realtor.dashboard') }}" class="btn btn-outline mt-5 w-full sm:w-auto">
                    Access my Realtor Dashboard <x-icon name="arrow" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>
</x-layouts.standalone>
