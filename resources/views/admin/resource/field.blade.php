{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    One form control, chosen by $field['type']. Expects $field and $model.
--}}
@php
    $name = $field['name'];
    $type = $field['type'];
    $id = 'f-'.$name;
    $value = old($name, $model->{$name} ?? ($field['default'] ?? null));
    $span = ! empty($field['full']) || in_array($type, ['textarea', 'richtext', 'image', 'images', 'lines'], true) ? 'form-full' : '';
    $required = str_contains(json_encode($field['rules'] ?? ''), 'required') || (! empty($field['required']));
@endphp

<div class="{{ $span }}">
    @if ($type === 'toggle')
        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-ink/10 bg-page px-4 py-3.5">
            <span>
                <span class="block text-sm font-bold text-ink">{{ $field['label'] }}</span>
                @if (! empty($field['help']))<span class="mt-0.5 block text-xs text-mute">{{ $field['help'] }}</span>@endif
            </span>
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" name="{{ $name }}" value="1" class="sr-only" @checked((bool) $value)>
            <span class="switch" aria-hidden="true"></span>
        </label>
    @else
        <label for="{{ $id }}" class="field-label">{{ $field['label'] }} @if ($required)<span class="text-flag-500">*</span>@endif</label>

        @switch($type)
            @case('textarea')
                <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 6 }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="field">{{ $value }}</textarea>
                @break

            @case('richtext')
                <div class="rte" data-rte @if (! empty($field['compact'])) data-rte-compact @endif data-placeholder="{{ $field['placeholder'] ?? 'Start writing…' }}">
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 10 }}" class="field">{{ $value }}</textarea>
                </div>
                @break

            @case('lines')
                <textarea id="{{ $id }}" name="{{ $name }}" rows="5" placeholder="{{ $field['placeholder'] ?? 'One item per line' }}" class="field">{{ is_array($value) ? implode("\n", $value) : $value }}</textarea>
                @break

            @case('select')
                <select id="{{ $id }}" name="{{ $name }}" class="field">
                    @if (! empty($field['blank']))<option value="">{{ $field['blank'] }}</option>@endif
                    @foreach ($field['options'] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('number')
                <input id="{{ $id }}" type="text" inputmode="decimal" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="field">
                @break

            @case('datetime')
                <input id="{{ $id }}" type="datetime-local" name="{{ $name }}" value="{{ $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}" class="field">
                @break

            @case('image')
                @if ($value)
                    <div class="mb-3 flex items-center gap-4">
                        <span class="thumb h-24 w-32 shrink-0"><img src="{{ \App\Models\SiteSetting::media($value) }}" alt="" class="h-full w-full object-cover"></span>
                        <label class="flex items-center gap-2 text-sm font-semibold text-mute"><input type="checkbox" name="remove_{{ $name }}" value="1" class="rounded border-ink/30 text-flag-500 focus:ring-flag-500/30"> Remove image</label>
                    </div>
                @endif
                <label class="dropzone cursor-pointer">
                    <x-icon name="upload" class="h-6 w-6" />
                    <span class="font-semibold">Tap to choose{{ $value ? ' a replacement' : ' an image' }}, or drop it here</span>
                    <span class="text-xs">JPG, PNG or WebP · resized and optimised automatically</span>
                    <input id="{{ $id }}" type="file" name="{{ $name }}" accept="image/*" class="sr-only" data-preview="#pv-{{ $name }}">
                </label>
                <div id="pv-{{ $name }}" class="mt-3 grid max-w-xs grid-cols-1"></div>
                @break

            @case('images')
                @php $existing = (array) ($model->{$name} ?? []); @endphp
                @if ($existing)
                    <div class="mb-3 grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                        @foreach ($existing as $path)
                            <label class="thumb group cursor-pointer">
                                <img src="{{ \App\Models\SiteSetting::media($path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                <input type="checkbox" name="remove_{{ $name }}[]" value="{{ $path }}" class="peer sr-only">
                                <span class="absolute inset-0 flex items-center justify-center bg-flag-500/80 text-xs font-bold uppercase tracking-wider text-white opacity-0 transition peer-checked:opacity-100">Remove</span>
                                <span class="absolute bottom-1.5 right-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-navy-950/70 text-white opacity-0 transition group-hover:opacity-100 peer-checked:hidden"><x-icon name="trash" class="h-3.5 w-3.5" /></span>
                            </label>
                        @endforeach
                    </div>
                    <p class="hint mb-3">Tap a photo to mark it for removal, then save.</p>
                @endif
                <label class="dropzone cursor-pointer">
                    <x-icon name="upload" class="h-6 w-6" />
                    <span class="font-semibold">Add photos: tap to choose several, or drop them here</span>
                    <span class="text-xs">Up to {{ $field['max'] ?? 20 }} images · optimised automatically</span>
                    <input id="{{ $id }}" type="file" name="{{ $name }}[]" accept="image/*" multiple class="sr-only" data-preview="#pv-{{ $name }}">
                </label>
                <div id="pv-{{ $name }}" class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6"></div>
                @break

            @default
                <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if (! empty($field['list'])) list="dl-{{ $name }}" @endif class="field">
                @if (! empty($field['list']))
                    <datalist id="dl-{{ $name }}">@foreach ($field['list'] as $opt)<option value="{{ $opt }}"></option>@endforeach</datalist>
                @endif
        @endswitch

        @if (! empty($field['help']))<p class="hint">{{ $field['help'] }}</p>@endif
        @error($name)<p class="err">{{ $message }}</p>@enderror
        @error($name.'.*')<p class="err">{{ $message }}</p>@enderror
    @endif
</div>
