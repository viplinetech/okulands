{{-- One row of an <x-admin.repeater>. Expects $name, $fields, $index, $row. --}}
<div data-repeater-row class="repeater-row relative rounded-2xl border border-ink/10 bg-page p-4">
    <button type="button" data-repeater-remove class="icon-action icon-action-danger absolute right-3 top-3 !h-8 !w-8" aria-label="Remove"><x-icon name="x" class="h-3.5 w-3.5" /></button>
    <div class="grid gap-3 pr-10 sm:grid-cols-2">
        @foreach ($fields as $f)
            @php $fid = $name.'-'.$index.'-'.$f['key']; $val = $row[$f['key']] ?? ''; @endphp
            <div class="{{ $f['span'] ?? '' }}">
                <label for="{{ $fid }}" class="field-label !mb-1">{{ $f['label'] }}</label>
                @if (($f['type'] ?? 'text') === 'textarea')
                    <textarea id="{{ $fid }}" name="{{ $name }}[{{ $index }}][{{ $f['key'] }}]" rows="2" class="field !py-2.5 text-sm">{{ $val }}</textarea>
                @else
                    <input id="{{ $fid }}" name="{{ $name }}[{{ $index }}][{{ $f['key'] }}]" value="{{ $val }}" @if (($f['type'] ?? '') === 'number') inputmode="numeric" @endif class="field !py-2.5 text-sm">
                @endif
            </div>
        @endforeach
    </div>
</div>
