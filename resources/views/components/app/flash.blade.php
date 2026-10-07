{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Renders session flash messages: success / status / warning / error.
--}}
@foreach ([['success', 'flash-success', 'check-circle'], ['status', 'flash-success', 'check-circle'], ['warning', 'flash-warning', 'alert'], ['error', 'flash-error', 'alert']] as [$key, $class, $icon])
    @if (session($key))
        <div role="{{ $key === 'error' ? 'alert' : 'status' }}" class="flash {{ $class }}" @if ($class === 'flash-success') data-autohide @endif>
            <x-icon :name="$icon" class="mt-px h-5 w-5 shrink-0" />
            <span>{{ session($key) }}</span>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div role="alert" class="flash flash-error">
        <x-icon name="alert" class="mt-px h-5 w-5 shrink-0" />
        <div>
            <p>Please check the following:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-4 font-medium">
                @foreach (collect($errors->all())->unique()->take(4) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
