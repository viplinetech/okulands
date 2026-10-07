@props(['status'])

@if ($status)
    <div role="status" {{ $attributes->merge(['class' => 'rounded-2xl border border-brand/30 bg-brand/10 px-5 py-4 text-sm font-medium text-ink']) }}>
        {{ $status }}
    </div>
@endif
