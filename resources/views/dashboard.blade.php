<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <x-slot name="header">
        <h4 class="mb-0">Dashboard</h4>
    </x-slot>

    <div class="container">
        <p>Welcome back, <strong>{{ Auth::user()->name }}</strong>.</p>

        <div class="row g-3 mb-4">
            @foreach ($stats as $label => $value)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-3 fw-bold">{{ number_format($value) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Your latest ads</h5>
            <a href="{{ route('my-listings.index') }}" class="small">Manage all</a>
        </div>

        @if ($recent->isEmpty())
            <div class="alert alert-info">
                You haven't posted any ads yet. <a href="{{ route('my-listings.create') }}">Post your first ad</a>
            </div>
        @else
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($recent as $listing)
                    <div class="col">
                        <x-listing-card :listing="$listing" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
