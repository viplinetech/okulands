{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Accessible accordion; height animates via grid-template-rows (see initAccordions).
    Without JS every answer stays open (collapsing is gated behind the .js class).
--}}
@props(['items' => []])

<div class="divide-y divide-ink/10 border-y border-ink/10">
    @foreach ($items as $index => $item)
        <div class="acc-item {{ $index === 0 ? 'is-open' : '' }}">
            <h3>
                <button type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" class="flex w-full items-center justify-between gap-6 py-5 text-left sm:py-6">
                    <span class="font-serif text-xl leading-snug text-ink sm:text-2xl">{{ $item['q'] }}</span>
                    <span class="acc-plus flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-ink/20 text-ink">
                        <x-icon name="plus" class="h-4 w-4" />
                    </span>
                </button>
            </h3>
            <div class="acc-body">
                <div>
                    <x-prose :html="$item['a']" class="max-w-xl pb-7 text-[0.95rem] leading-relaxed text-mute" />
                </div>
            </div>
        </div>
    @endforeach
</div>
