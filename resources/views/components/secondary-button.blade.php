<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline disabled:opacity-40']) }}>
    {{ $slot }}
</button>
