@props(['status'])

@php
    $class = match ($status) {
        'active' => 'bg-success',
        'sold' => 'bg-warning text-dark',
        default => 'bg-secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$class}"]) }}>{{ ucfirst($status) }}</span>
