{{-- One team member row (settings). Expects $index, $row. Photo uploads post as team_files[INDEX]. --}}
@php $photo = \App\Models\SiteSetting::media($row['photo'] ?? null); @endphp
<div data-repeater-row class="repeater-row relative rounded-2xl border border-ink/10 bg-page p-4">
    <button type="button" data-repeater-remove class="icon-action icon-action-danger absolute right-3 top-3 !h-8 !w-8" aria-label="Remove"><x-icon name="x" class="h-3.5 w-3.5" /></button>
    <div class="flex flex-col gap-4 pr-10 sm:flex-row">
        <div class="shrink-0">
            <span class="thumb block h-24 w-24 !aspect-square !rounded-2xl bg-soft">@if ($photo)<img src="{{ $photo }}" alt="" class="h-full w-full object-cover">@else<span class="flex h-full items-center justify-center text-mute"><x-icon name="user" class="h-8 w-8" /></span>@endif</span>
            <input type="hidden" name="team[{{ $index }}][photo]" value="{{ $row['photo'] ?? '' }}">
            <label class="mt-2 block cursor-pointer text-center text-xs font-bold text-brand">Change photo<input type="file" name="team_files[{{ $index }}]" accept="image/*" class="sr-only"></label>
            @if ($photo)
                <input type="checkbox" id="team_remove_{{ $index }}" name="team_remove[{{ $index }}]" value="1" class="sr-only" tabindex="-1">
                <button type="button" class="mt-1 flex w-full items-center justify-center gap-1.5 text-[0.7rem] font-semibold text-flag-500" data-remove-photo data-remove-target="team_remove_{{ $index }}" data-remove-label="team member"><x-icon name="trash" class="h-3 w-3" /> <span data-remove-text>Remove photo</span></button>
            @endif
        </div>
        <div class="grid flex-1 gap-3 sm:grid-cols-2">
            <div><label class="field-label !mb-1">Name</label><input name="team[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}" class="field !py-2.5 text-sm"></div>
            <div><label class="field-label !mb-1">Role</label><input name="team[{{ $index }}][role]" value="{{ $row['role'] ?? '' }}" placeholder="e.g. Head of Construction" class="field !py-2.5 text-sm"></div>
            <div class="sm:col-span-2"><label class="field-label !mb-1">Short bio</label><textarea name="team[{{ $index }}][bio]" rows="2" class="field !py-2.5 text-sm">{{ $row['bio'] ?? '' }}</textarea></div>
        </div>
    </div>
</div>
