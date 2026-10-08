<x-layouts.admin :title="($creating ? 'New ' : 'Edit ').$c['singular']">
    <x-app.page-header :title="($creating ? 'New ' : 'Edit ').$c['singular']" kicker="Website content">
        @if ($creating && ! empty($c['fullAi']))
            <button type="button" class="btn btn-primary btn-sm" data-ai-full data-url="{{ route($c['fullAi']['route']) }}">
                <x-icon name="sparkle" class="h-4 w-4" /> <span data-ai-full-label>{{ $c['fullAi']['label'] }}</span>
            </button>
        @endif
        <a href="{{ route($c['route'].'.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
    </x-app.page-header>

    @if ($creating && ! empty($c['fullAi']))
        <p class="-mt-3 mb-4 text-xs text-mute" data-ai-full-status></p>
    @endif

    @error('upload')<div class="flash flash-error"><x-icon name="alert" class="mt-px h-5 w-5 shrink-0" /><span>{{ $message }}</span></div>@enderror

    <form method="POST" enctype="multipart/form-data"
          action="{{ $creating ? route($c['route'].'.store') : route($c['route'].'.update', $model) }}">
        @csrf
        @unless ($creating) @method('PUT') @endunless

        @php $sections = collect($c['fields'])->groupBy(fn ($f) => $f['section'] ?? 'Details'); @endphp

        <div class="space-y-4">
            @foreach ($sections as $section => $fields)
                <section class="card">
                    <h2 class="card-title">{{ $section }}</h2>
                    <div class="form-grid mt-5">
                        @foreach ($fields as $field)
                            @include('admin.resource.field', ['field' => $field, 'model' => $model])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="sticky bottom-3 z-30 mt-5 flex justify-end gap-2 rounded-3xl border border-ink/10 bg-card/90 p-3 shadow-soft backdrop-blur-xl lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <a href="{{ route($c['route'].'.index') }}" class="btn btn-outline btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">{{ $creating ? 'Create '.$c['singular'] : 'Save changes' }} <x-icon name="check" class="h-4 w-4" /></button>
        </div>
    </form>
</x-layouts.admin>
