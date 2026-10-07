<x-layouts.admin :title="$lead->name">
    @php
        $tel = $lead->phone ? preg_replace('/[^0-9+]/', '', $lead->phone) : null;
    @endphp
    <x-app.page-header :title="$lead->name" kicker="Lead">
        <a href="{{ route('admin.leads.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
        <a href="{{ route('admin.sales.create', ['lead' => $lead->id]) }}" class="btn btn-primary btn-sm"><x-icon name="tag" class="h-4 w-4" /> Record sale</a>
    </x-app.page-header>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="card-title">Enquiry</h2>
                    <x-app.badge :status="$lead->status" />
                </div>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="field-label !mb-1">Phone</dt><dd class="font-bold text-ink">{{ $lead->phone ?: '—' }}</dd></div>
                    <div><dt class="field-label !mb-1">Email</dt><dd class="break-all font-bold text-ink">{{ $lead->email ?: '—' }}</dd></div>
                    <div><dt class="field-label !mb-1">Type</dt><dd class="font-bold text-ink">{{ ucfirst($lead->type) }}</dd></div>
                    <div><dt class="field-label !mb-1">Received</dt><dd class="font-bold text-ink">{{ $lead->created_at->format('M j, Y · g:ia') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="field-label !mb-1">Property</dt><dd class="font-bold text-ink">@if ($lead->property)<a href="{{ route('properties.show', $lead->property->slug) }}" target="_blank" class="text-brand hover:underline">{{ $lead->property->title }}</a>@else General enquiry @endif</dd></div>
                </dl>
                @if ($lead->message)
                    <div class="mt-5 rounded-2xl bg-soft p-4 text-sm leading-relaxed text-ink">{{ $lead->message }}</div>
                @endif
                <div class="mt-5 flex flex-wrap gap-2">
                    @if ($tel)
                        <a href="tel:{{ $tel }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="h-4 w-4" /> Call</a>
                        <a href="https://wa.me/{{ ltrim(preg_replace('/^0/', '234', $tel), '+') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><x-icon name="whatsapp" class="h-4 w-4 text-[#25D366]" /> WhatsApp</a>
                    @endif
                    @if ($lead->email)<a href="mailto:{{ $lead->email }}" class="btn btn-outline btn-sm"><x-icon name="mail" class="h-4 w-4" /> Email</a>@endif
                </div>
            </section>
        </div>

        <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="card space-y-4 self-start">
            @csrf @method('PUT')
            <h2 class="card-title">Manage</h2>
            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field">@foreach ($statuses as $s)<option value="{{ $s }}" @selected($lead->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
            </div>
            <div>
                <label for="referrer_id" class="field-label">Referred by</label>
                <select id="referrer_id" name="referrer_id" class="field">
                    <option value="">No realtor (direct)</option>
                    @foreach ($realtors as $r)<option value="{{ $r->id }}" @selected($lead->referrer_id === $r->id)>{{ $r->name }}</option>@endforeach
                </select>
                <p class="hint">Re-assign if the lead was credited to the wrong realtor.</p>
            </div>
            <div>
                <label for="notes" class="field-label">Internal notes</label>
                <textarea id="notes" name="notes" rows="5" class="field" placeholder="Call outcome, preferred date…">{{ old('notes', $lead->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-full">Save</button>
        </form>
    </div>
</x-layouts.admin>
