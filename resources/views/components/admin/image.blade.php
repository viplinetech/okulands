{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Single image picker with preview and a remove option. Posts <name> (file) and remove_<name> (checkbox).
--}}
@props(['name', 'label', 'current' => null, 'help' => null, 'wide' => false])
@php $url = \App\Models\SiteSetting::media($current); @endphp
<div {{ $attributes }}>
    <span class="field-label">{{ $label }}</span>
    <div class="flex items-start gap-4">
        @if ($url)
            <div class="shrink-0" data-photo>
                <span class="thumb block {{ $wide ? 'h-20 w-36' : 'h-20 w-20' }} !aspect-auto bg-soft"><img src="{{ $url }}" alt="" class="h-full w-full object-cover"></span>
                {{-- The real checkbox is hidden; the button asks for confirmation and ticks it (see initRemovePhoto). --}}
                <input type="checkbox" id="remove_{{ $name }}" name="remove_{{ $name }}" value="1" class="sr-only" tabindex="-1">
                <button type="button" class="btn btn-ghost btn-sm mt-2 !px-3 !py-1.5 text-xs text-flag-500" data-remove-photo data-remove-target="remove_{{ $name }}" data-remove-label="{{ $label }}">
                    <x-icon name="trash" class="h-3.5 w-3.5" /> <span data-remove-text>Remove photo</span>
                </button>
            </div>
        @endif
        <label class="dropzone min-w-0 flex-1 cursor-pointer !py-4">
            <x-icon name="upload" class="h-5 w-5" />
            <span class="text-xs font-semibold">{{ $url ? 'Replace image' : 'Choose an image' }}</span>
            <input type="file" name="{{ $name }}" accept="image/*" class="sr-only" data-preview="#pv-{{ $name }}">
        </label>
    </div>
    <div id="pv-{{ $name }}" class="mt-2 grid max-w-[10rem] grid-cols-1"></div>
    @if ($help)<p class="hint">{{ $help }}</p>@endif
    @error($name)<p class="err">{{ $message }}</p>@enderror
</div>
