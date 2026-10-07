{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Add/remove rows editor (behaviour in shell.js: initRepeaters).
    fields = [['key' => 'title', 'label' => 'Title', 'type' => 'text|textarea|number', 'span' => 'sm:col-span-2']]
    Rows post as  name[INDEX][key].
--}}
@props(['name', 'rows' => [], 'fields', 'add' => 'Add row'])
<div data-repeater class="space-y-3">
    <div data-repeater-list class="space-y-3">
        @foreach (collect($rows)->values() as $i => $row)
            @include('admin.partials.repeater-row', ['index' => $i, 'row' => (array) $row])
        @endforeach
    </div>
    <template>@include('admin.partials.repeater-row', ['index' => '__INDEX__', 'row' => []])</template>
    <button type="button" data-repeater-add class="btn btn-outline btn-sm"><x-icon name="plus" class="h-4 w-4" /> {{ $add }}</button>
</div>
