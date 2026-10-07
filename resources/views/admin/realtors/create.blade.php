<x-layouts.admin title="Add realtor">
    <x-app.page-header title="Add a realtor" kicker="People" subtitle="A secure temporary password is generated for you to share. They can change it after signing in.">
        <a href="{{ route('admin.realtors.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
    </x-app.page-header>

    <form method="POST" action="{{ route('admin.realtors.store') }}" class="card max-w-3xl">
        @csrf
        <div class="form-grid">
            <div class="form-full">
                <label for="name" class="field-label">Full name <span class="text-flag-500">*</span></label>
                <input id="name" name="name" required value="{{ old('name') }}" class="field">
                @error('name')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="field-label">Email <span class="text-flag-500">*</span></label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}" class="field">
                @error('email')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="field-label">Phone</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field">
                @error('phone')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div class="form-full">
                <label for="referred_by" class="field-label">Sponsor (upline)</label>
                <select id="referred_by" name="referred_by" class="field">
                    <option value="">No sponsor</option>
                    @foreach ($sponsors as $s)<option value="{{ $s->id }}" @selected(old('referred_by') == $s->id)>{{ $s->name }}</option>@endforeach
                </select>
                <p class="hint">The sponsor earns a share of this realtor&rsquo;s sales.</p>
                @error('referred_by')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('admin.realtors.index') }}" class="btn btn-outline btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Create realtor <x-icon name="check" class="h-4 w-4" /></button>
        </div>
    </form>
</x-layouts.admin>
