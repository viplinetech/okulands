<x-layouts.realtor title="Profile">
    <x-app.page-header title="Your profile" kicker="Account" subtitle="Keep your details and payout account up to date so you are always paid on time." />

    <form method="POST" action="{{ route('realtor.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PUT')

        {{-- Identity --}}
        <section class="card">
            <h2 class="card-title">Personal details</h2>
            <div class="mt-5 flex flex-col gap-5 sm:flex-row">
                <div class="shrink-0">
                    <label class="dropzone !h-32 !w-32 cursor-pointer !rounded-full !p-0 overflow-hidden" title="Change photo">
                        <span id="avatar-preview" class="absolute inset-0 flex items-center justify-center [&>.thumb]:h-full [&>.thumb]:w-full [&>.thumb]:rounded-none [&>.thumb]:border-0 [&>.thumb]:!aspect-auto">
                            @if ($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="" class="h-full w-full object-cover">@else<span class="font-serif text-4xl text-brand">{{ $user->initials() }}</span>@endif
                        </span>
                        <input type="file" name="avatar" accept="image/*" class="sr-only" data-preview="#avatar-preview">
                        <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-navy-950/60 py-1.5 text-[0.62rem] font-bold uppercase tracking-wider text-white">Change</span>
                    </label>
                    @error('avatar')<p class="err text-center">{{ $message }}</p>@enderror
                </div>
                <div class="form-grid flex-1">
                    <div class="form-full">
                        <label for="name" class="field-label">Full name</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="field">
                        @error('name')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="field-label">Phone / WhatsApp</label>
                        <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone', $user->phone) }}" required autocomplete="tel" class="field">
                        @error('phone')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label">Email <span class="normal-case tracking-normal opacity-60">(contact support to change)</span></label>
                        <input value="{{ $user->email }}" disabled class="field !bg-soft opacity-70">
                    </div>
                    <div class="form-full">
                        <label class="field-label">Your referral code</label>
                        <div class="flex gap-2">
                            <input value="{{ $user->referral_code }}" readonly class="field flex-1 font-mono">
                            <button type="button" data-copy="{{ $user->referral_code }}" class="btn btn-outline btn-sm shrink-0"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Payout account --}}
        <section id="payout" class="card scroll-mt-24">
            <div class="flex items-center gap-3">
                <span class="stat-icon"><x-icon name="bank" class="h-5 w-5" /></span>
                <div>
                    <h2 class="card-title">Payout account</h2>
                    <p class="text-xs text-mute">Your commission is paid here. Stored encrypted; only the last 4 digits are ever shown.</p>
                </div>
            </div>
            <div class="form-grid mt-5">
                <div class="form-full">
                    <label for="bank_name" class="field-label">Bank</label>
                    <select id="bank_name" name="bank_name" class="field">
                        <option value="">Select your bank</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank }}" @selected(old('bank_name', $user->bank_name) === $bank)>{{ $bank }}</option>
                        @endforeach
                    </select>
                    @error('bank_name')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account_number" class="field-label">Account number</label>
                    <input id="account_number" name="account_number" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ old('account_number', $user->account_number) }}" placeholder="10 digits" class="field font-mono tracking-widest">
                    @error('account_number')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="account_name" class="field-label">Account name</label>
                    <input id="account_name" name="account_name" autocomplete="off" value="{{ old('account_name', $user->account_name) }}" class="field">
                    @error('account_name')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary w-full sm:w-auto">Save changes <x-icon name="check" class="h-4 w-4" /></button>
        </div>
    </form>
</x-layouts.realtor>
