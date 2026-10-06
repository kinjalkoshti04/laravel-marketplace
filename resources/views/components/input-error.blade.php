@props(['messages'])

@if ($messages)
    <div data-server-error {{ $attributes->merge(['class' => 'invalid-feedback d-block']) }}>
        @foreach ((array) $messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
