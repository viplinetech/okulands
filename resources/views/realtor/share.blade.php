<x-layouts.realtor title="Share">
    @php
        $wa = 'https://wa.me/?text='.rawurlencode($message);
        $fb = 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($link);
        $tg = 'https://t.me/share/url?url='.rawurlencode($link).'&text='.rawurlencode('Verified land and property across Nigeria');
        $sms = 'sms:?&body='.rawurlencode($message);
        $mail = 'mailto:?subject='.rawurlencode('Verified land and property in Nigeria').'&body='.rawurlencode($message);
    @endphp

    <x-app.page-header title="Share & earn" kicker="Your referral link" subtitle="Anyone who enquires or buys through your link is tagged to you for a year, even if they call the office later." />

    <div class="grid gap-4 lg:grid-cols-5">
        {{-- Link + share buttons --}}
        <section class="stat stat-hero !p-5 sm:!p-7 lg:col-span-3">
            <p class="stat-label !mt-0">Your personal link</p>
            <p class="mt-2 break-all font-mono text-base text-white sm:text-lg">{{ preg_replace('#^https?://#', '', $link) }}</p>
            <div class="mt-5 grid grid-cols-2 gap-2">
                <button type="button" data-copy="{{ $link }}" class="btn btn-sky btn-sm"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy link</span></button>
                <button type="button" data-share="{{ $link }}" data-title="Oku Lands & Properties" data-text="Verified land and property across Nigeria." class="btn btn-ghost btn-sm"><x-icon name="send" class="h-4 w-4" /><span data-label>Share…</span></button>
            </div>

            <p class="stat-label mt-6">Send it on</p>
            <div class="mt-3 grid grid-cols-5 gap-2">
                @foreach ([[$wa, 'whatsapp', 'WhatsApp', 'text-[#25D366]'], [$fb, 'facebook', 'Facebook', 'text-[#5b9bf0]'], [$tg, 'send', 'Telegram', 'text-sky-300'], [$sms, 'phone', 'SMS', 'text-white'], [$mail, 'mail', 'Email', 'text-white']] as [$href, $icon, $label, $color])
                    <a href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif class="flex min-w-0 flex-col items-center gap-1.5 rounded-2xl border border-white/10 bg-white/[0.06] px-0.5 py-3 text-[0.6rem] font-bold tracking-tight text-white/80 sm:text-[0.68rem] transition active:scale-95 hover:bg-white/10">
                        <x-icon :name="$icon" class="h-6 w-6 {{ $color }}" />{{ $label }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- QR --}}
        <section class="card flex flex-col items-center text-center lg:col-span-2">
            <h2 class="card-title">Scan to open</h2>
            <p class="mt-1 text-xs text-mute">Perfect for flyers, banners and in-person meetings.</p>
            <div class="mt-4 rounded-3xl bg-white p-3 shadow-soft [&_svg]:h-44 [&_svg]:w-44">{!! $qr !!}</div>
        </section>
    </div>

    {{-- Ready-made message --}}
    <section class="card mt-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="card-title">Ready-to-send message</h2>
            <button type="button" data-copy="{{ $message }}" class="btn btn-outline btn-sm"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy</span></button>
        </div>
        <p class="mt-4 rounded-2xl bg-soft p-4 text-sm leading-relaxed text-ink">{{ $message }}</p>
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="card">
            <h2 class="card-title">Link activity</h2>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                @foreach ([['Last 7 days', $clicks7], ['Last 30 days', $clicks30], ['All time', $clicksAll]] as [$label, $n])
                    <div class="rounded-2xl bg-soft px-2 py-4"><p class="font-sans text-3xl font-extrabold tracking-tight text-ink [font-variant-numeric:tabular-nums]">{{ number_format($n) }}</p><p class="mt-1 text-[0.62rem] font-bold uppercase tracking-wider text-mute">{{ $label }}</p></div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2 class="card-title">Grow your team</h2>
            <p class="mt-2 text-sm leading-relaxed text-mute">Invite other ambitious people to become realtors. You earn a share of every sale they make, forever.</p>
            <p class="mt-3 truncate rounded-2xl bg-soft px-4 py-3 font-mono text-xs text-ink">{{ preg_replace('#^https?://#', '', $inviteLink) }}</p>
            <button type="button" data-copy="{{ $inviteLink }}" class="btn btn-primary btn-sm mt-3 w-full"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy invite link</span></button>
        </section>
    </div>

    <section class="card mt-4">
        <h2 class="card-title">Tips that convert</h2>
        <ul class="mt-4 grid gap-3 text-sm text-mute sm:grid-cols-2">
            @foreach (['Share on WhatsApp status and groups at 7–9pm, when people are online.', 'Use a specific property link from Listings instead of the general link.', 'Reply to new leads within 10 minutes. It is the biggest factor in closing.', 'Offer a free inspection: buyers who see the land in person buy far more often.'] as $tip)
                <li class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="sparkle" class="h-3.5 w-3.5" /></span>{{ $tip }}</li>
            @endforeach
        </ul>
    </section>
</x-layouts.realtor>
