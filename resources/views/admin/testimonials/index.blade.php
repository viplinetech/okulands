<x-layouts.admin title="Reviews">
    <x-app.page-header title="Client reviews" kicker="Website content" subtitle="Reviews submitted on the website wait here until you publish them." />

    <div class="tabs mb-4">
        <a href="{{ route('admin.testimonials.index') }}" class="tab {{ ! $filter ? 'is-active' : '' }}">All</a>
        <a href="{{ route('admin.testimonials.index', ['show' => 'pending']) }}" class="tab {{ $filter === 'pending' ? 'is-active' : '' }}">Waiting <span class="opacity-60">{{ $pending }}</span></a>
        <a href="{{ route('admin.testimonials.index', ['show' => 'approved']) }}" class="tab {{ $filter === 'approved' ? 'is-active' : '' }}">Published <span class="opacity-60">{{ $approved }}</span></a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-3 lg:col-span-2">
            @forelse ($testimonials as $t)
                <article class="card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-ink">{{ $t->client_name }} @if ($t->client_role)<span class="font-normal text-mute">· {{ $t->client_role }}</span>@endif</p>
                            <div class="mt-1 flex gap-0.5 text-brand">@for ($i = 0; $i < $t->rating; $i++)<x-icon name="star" class="h-4 w-4 fill-current" />@endfor</div>
                        </div>
                        <x-app.badge :status="$t->approved ? 'published' : 'pending'">{{ $t->approved ? 'Published' : 'Waiting' }}</x-app.badge>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-ink">&ldquo;{{ $t->content }}&rdquo;</p>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs text-mute">{{ $t->created_at->format('M j, Y') }}</span>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.testimonials.update', $t) }}">@csrf @method('PUT')
                                <input type="hidden" name="approved" value="{{ $t->approved ? 0 : 1 }}">
                                <button type="submit" class="btn {{ $t->approved ? 'btn-outline' : 'btn-primary' }} btn-sm">{{ $t->approved ? 'Hide' : 'Publish' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" data-confirm="Delete this review permanently?">@csrf @method('DELETE')
                                <button type="submit" class="icon-action icon-action-danger" aria-label="Delete"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="card-flush"><x-app.empty icon="star" title="No reviews here" text="Reviews from the About page appear here for your approval." /></div>
            @endforelse
            <div class="pt-2">{{ $testimonials->links('pagination.premium') }}</div>
        </div>

        <form method="POST" action="{{ route('admin.testimonials.store') }}" class="card space-y-4 self-start">
            @csrf
            <h2 class="card-title">Add a review</h2>
            <p class="-mt-2 text-xs text-mute">For feedback you received by phone or in person. It is published immediately.</p>
            <div><label for="client_name" class="field-label">Client name</label><input id="client_name" name="client_name" required value="{{ old('client_name') }}" class="field">@error('client_name')<p class="err">{{ $message }}</p>@enderror</div>
            <div><label for="client_role" class="field-label">Role / location</label><input id="client_role" name="client_role" value="{{ old('client_role') }}" class="field"></div>
            <div>
                <label for="rating" class="field-label">Rating</label>
                <select id="rating" name="rating" class="field">@foreach ([5, 4, 3, 2, 1] as $n)<option value="{{ $n }}">{{ $n }} {{ $n === 1 ? 'star' : 'stars' }}</option>@endforeach</select>
            </div>
            <div><label for="content" class="field-label">Review</label><textarea id="content" name="content" rows="4" required class="field">{{ old('content') }}</textarea>@error('content')<p class="err">{{ $message }}</p>@enderror</div>
            <button type="submit" class="btn btn-primary btn-sm w-full">Publish review</button>
        </form>
    </div>
</x-layouts.admin>
