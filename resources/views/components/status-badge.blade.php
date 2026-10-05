@props(['status'])

@php
    $classes = match ($status) {
        'active' => 'bg-green-100 text-green-800',
        'sold' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-200 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ ucfirst($status) }}
</span>
