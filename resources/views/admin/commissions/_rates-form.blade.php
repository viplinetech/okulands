{{-- Commission rates form, shared by Commissions and Business settings. --}}
    <form method="POST" action="{{ route('admin.commissions.rates') }}" class="card mt-4">
        @csrf @method('PUT')
        <div class="flex items-center gap-3">
            <span class="stat-icon"><x-icon name="sliders" class="h-5 w-5" /></span>
            <div>
                <h2 class="card-title">Commission rates</h2>
                <p class="text-xs text-mute">Tier 1 is the realtor who closes the sale; tier 2 is their sponsor; tier 3 the sponsor&rsquo;s sponsor, and so on. New rates apply to sales approved from now on.</p>
            </div>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($rates as $rate)
                <div class="rounded-2xl border border-ink/10 bg-page p-3.5">
                    <label for="rate-{{ $rate->tier }}" class="field-label !mb-1.5">Tier {{ $rate->tier }}{{ $rate->tier === 1 ? ' · closes the sale' : '' }}</label>
                    <div class="flex items-center gap-2">
                        <input id="rate-{{ $rate->tier }}" name="rates[{{ $rate->tier }}]" inputmode="decimal" value="{{ old('rates.'.$rate->tier, rtrim(rtrim(number_format($rate->rate, 2), '0'), '.')) }}" class="field !py-2.5 text-lg font-bold">
                        <span class="text-lg font-bold text-mute">%</span>
                    </div>
                </div>
            @endforeach
            <div class="rounded-2xl border border-dashed border-ink/20 p-3.5">
                <label for="new_rate" class="field-label !mb-1.5">Add tier {{ ($rates->max('tier') ?? 0) + 1 }}</label>
                <div class="flex items-center gap-2">
                    <input id="new_rate" name="new_rate" inputmode="decimal" placeholder="e.g. 1" class="field !py-2.5 text-lg">
                    <span class="text-lg font-bold text-mute">%</span>
                </div>
            </div>
        </div>
        @error('rates.*')<p class="err">{{ $message }}</p>@enderror
        <div class="mt-5 flex justify-end"><button type="submit" class="btn btn-primary btn-sm">Save rates</button></div>
    </form>
