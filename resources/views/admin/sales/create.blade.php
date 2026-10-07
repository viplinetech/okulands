<x-layouts.admin title="Record sale">
    @php $preLead = $lead; @endphp
    <x-app.page-header title="Record a sale" kicker="Sales" subtitle="Pick the property and the realtor who earns the credit. Approve it next to create commissions.">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
    </x-app.page-header>

    <form method="POST" action="{{ route('admin.sales.store') }}" class="space-y-4">
        @csrf
        @if ($preLead)<input type="hidden" name="lead_id" value="{{ $preLead->id }}">@endif

        <section class="card">
            <h2 class="card-title">Sale</h2>
            <div class="form-grid mt-5">
                <div class="form-full">
                    <label for="property_id" class="field-label">Property <span class="text-flag-500">*</span></label>
                    <select id="property_id" name="property_id" required class="field" data-price-source>
                        <option value="">Select a property</option>
                        @foreach ($properties as $p)
                            <option value="{{ $p->id }}"
                                data-price="{{ (int) $p->price }}"
                                data-units-total="{{ $p->units_total ?? '' }}"
                                data-units-remaining="{{ $p->isMultiUnit() ? $p->unitsRemaining() : '' }}"
                                @selected(old('property_id', $preLead?->property_id) == $p->id)>
                                {{ $p->title }} · ₦{{ number_format($p->price) }}{{ $p->isMultiUnit() ? ' / unit · '.$p->unitsRemaining().' left' : '' }} ({{ $p->status }})
                            </option>
                        @endforeach
                    </select>
                    <p class="hint" data-units-hint hidden></p>
                    @error('property_id')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="realtor_id" class="field-label">Realtor credited <span class="text-flag-500">*</span></label>
                    <select id="realtor_id" name="realtor_id" required class="field">
                        <option value="">Select a realtor</option>
                        @foreach ($realtors as $r)<option value="{{ $r->id }}" @selected(old('realtor_id', $preLead?->referrer_id) == $r->id)>{{ $r->name }}</option>@endforeach
                    </select>
                    @error('realtor_id')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div data-units-field hidden>
                    <label for="quantity" class="field-label">Quantity (units/plots)</label>
                    <input id="quantity" name="quantity" type="number" min="1" step="1" value="{{ old('quantity', 1) }}" class="field" data-quantity-input>
                    @error('quantity')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="field-label">Sale amount (₦) <span class="text-flag-500">*</span></label>
                    <input id="amount" name="amount" inputmode="decimal" required value="{{ old('amount') }}" class="field" data-price-target>
                    <p class="hint">Filled automatically from the property price and quantity; change it if the buyer paid a different amount.</p>
                    @error('amount')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="reference" class="field-label">Receipt / reference</label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" class="field">
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="card-title">Buyer</h2>
            <div class="form-grid mt-5">
                <div class="form-full">
                    <label for="buyer_name" class="field-label">Buyer name <span class="text-flag-500">*</span></label>
                    <input id="buyer_name" name="buyer_name" required value="{{ old('buyer_name', $preLead?->name) }}" class="field">
                    @error('buyer_name')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="buyer_phone" class="field-label">Phone</label>
                    <input id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone', $preLead?->phone) }}" class="field">
                </div>
                <div>
                    <label for="buyer_email" class="field-label">Email</label>
                    <input id="buyer_email" name="buyer_email" type="email" value="{{ old('buyer_email', $preLead?->email) }}" class="field">
                    @error('buyer_email')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="form-full">
                    <label for="notes" class="field-label">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="field">{{ old('notes') }}</textarea>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.sales.index') }}" class="btn btn-outline btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Record sale <x-icon name="check" class="h-4 w-4" /></button>
        </div>
    </form>

    <script>
        // Pre-fill the amount from the chosen property's price × quantity (only while untouched by the admin),
        // show/hide the Quantity field for multi-unit listings, and warn when more is requested than remains.
        (function () {
            var sel = document.querySelector('[data-price-source]');
            var amt = document.querySelector('[data-price-target]');
            var qtyField = document.querySelector('[data-units-field]');
            var qty = document.querySelector('[data-quantity-input]');
            var hint = document.querySelector('[data-units-hint]');
            if (!sel || !amt || !qty) return;

            var touched = amt.value !== '';
            amt.addEventListener('input', function () { touched = true; });

            function recalc() {
                var o = sel.options[sel.selectedIndex];
                var price = o && o.dataset.price ? parseFloat(o.dataset.price) : null;
                var remaining = o && o.dataset.unitsRemaining !== '' ? parseInt(o.dataset.unitsRemaining, 10) : null;
                var isMultiUnit = remaining !== null && !isNaN(remaining);

                qtyField.hidden = !isMultiUnit;
                if (!isMultiUnit) qty.value = 1;

                var q = Math.max(1, parseInt(qty.value, 10) || 1);
                if (!touched && price !== null) amt.value = Math.round(price * q);

                if (isMultiUnit) {
                    qty.max = remaining;
                    hint.hidden = false;
                    hint.textContent = remaining + ' unit(s) left on this listing.';
                    hint.classList.toggle('err', q > remaining);
                    if (q > remaining) hint.textContent = 'Only ' + remaining + ' unit(s) are left — reduce the quantity.';
                } else {
                    hint.hidden = true;
                }
            }

            sel.addEventListener('change', recalc);
            qty.addEventListener('input', recalc);
            recalc();
        })();
    </script>
</x-layouts.admin>
