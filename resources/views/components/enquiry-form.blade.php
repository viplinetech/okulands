{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Lead form used on the Contact page and on each property page. Posts to LeadController@store.
--}}
@props(['interests' => [], 'propertyId' => null, 'selected' => null, 'compact' => false, 'button' => 'Send message', 'placeholder' => 'Tell us what you are looking for'])

@php $idp = $propertyId ? 'p' : 'c'; @endphp

<div id="enquire" class="scroll-mt-28">
    @if (session('status'))
        <div role="status" class="mb-6 flex items-start gap-3 rounded-2xl border border-brand/30 bg-brand/10 px-5 py-4 text-sm font-medium text-ink">
            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-brand" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form action="{{ route('contact.store') }}" method="POST" data-loading class="grid grid-cols-1 gap-5 {{ $compact ? '' : 'sm:grid-cols-2' }}" novalidate>
        @csrf
        @if ($propertyId)
            <input type="hidden" name="property_id" value="{{ $propertyId }}">
        @endif
        <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

        <div>
            <label for="{{ $idp }}-name" class="field-label">Full name <span class="text-flag-500" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <input id="{{ $idp }}-name" type="text" name="name" value="{{ old('name') }}" required aria-required="true" autocomplete="name" class="field">
            @error('name') <p class="mt-1.5 text-xs font-medium text-flag-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="{{ $idp }}-phone" class="field-label">Phone / WhatsApp <span class="text-flag-500" aria-hidden="true">*</span><span class="sr-only"> (required)</span></label>
            <div class="flex gap-2">
                <select name="phone_code" aria-label="Country code" class="field !w-auto shrink-0 !pr-8">
                    @foreach (\App\Support\Countries::dialCodes() as $code => $c)
                        <option value="{{ $code }}" @selected((old('phone_code', \App\Support\Countries::DEFAULT)) === $code)>{{ $c['flag'] }} {{ $code }}</option>
                    @endforeach
                </select>
                <input id="{{ $idp }}-phone" type="tel" name="phone" value="{{ old('phone') }}" required aria-required="true" autocomplete="tel-national" inputmode="numeric" pattern="[0-9]*" placeholder="8012345678" class="field min-w-0 flex-1" data-digits-only>
            </div>
            @error('phone') <p class="mt-1.5 text-xs font-medium text-flag-500">{{ $message }}</p> @enderror
        </div>
        <div class="{{ $compact ? '' : 'sm:col-span-2' }}">
            <label for="{{ $idp }}-email" class="field-label">Email <span class="font-normal normal-case tracking-normal text-mute">(optional)</span></label>
            <input id="{{ $idp }}-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="field">
            @error('email') <p class="mt-1.5 text-xs font-medium text-flag-500">{{ $message }}</p> @enderror
        </div>

        @if (! $propertyId && $interests)
            <div class="sm:col-span-2">
                <label for="c-interest" class="field-label">I&rsquo;m interested in</label>
                <select id="c-interest" name="interest" class="field">
                    <option value="">Select an option</option>
                    @foreach ($interests as $key => [$label])
                        <option value="{{ $key }}" @selected(old('interest', $selected) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="{{ $compact ? '' : 'sm:col-span-2' }}">
            <label for="{{ $idp }}-message" class="field-label">Message</label>
            <textarea id="{{ $idp }}-message" name="message" rows="{{ $compact ? 3 : 5 }}" placeholder="{{ $placeholder }}" class="field resize-none">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1.5 text-xs font-medium text-flag-500">{{ $message }}</p> @enderror
        </div>

        <div class="{{ $compact ? '' : 'sm:col-span-2' }}">
            <button type="submit" data-magnetic class="btn btn-primary w-full sm:w-auto {{ $compact ? 'sm:w-full' : '' }}">
                {{ $button }}
                <x-icon name="arrow" class="arrow h-4 w-4" />
            </button>
            <p class="mt-3 text-xs leading-relaxed text-mute">Your details are used only to reply to you. See our <a href="{{ route('legal', 'privacy-policy') }}" class="font-semibold text-brand hover:underline">Privacy Policy</a>.</p>
        </div>
    </form>
</div>
