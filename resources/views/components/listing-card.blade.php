@props(['listing'])

{{-- Expects ->withCardData() to be eager loaded. --}}
<div class="card h-100">
    <a href="{{ route('listings.show', $listing) }}">
        @if ($listing->primaryImage)
            <img src="{{ $listing->primaryImage->url }}" alt="{{ $listing->title }}" class="card-img-top listing-thumb" loading="lazy">
        @else
            <div class="card-img-top listing-thumb bg-light d-flex align-items-center justify-content-center text-muted small">No photo</div>
        @endif
    </a>
    <div class="card-body p-2">
        <h6 class="card-title fw-bold mb-1">{{ $listing->formatted_price }}</h6>
        <p class="card-text mb-1 text-truncate">
            <a href="{{ route('listings.show', $listing) }}" class="text-dark text-decoration-none">{{ $listing->title }}</a>
        </p>
        <div class="d-flex justify-content-between small text-muted">
            <span class="text-truncate">{{ $listing->location_label }}</span>
            <span class="text-nowrap ms-2">{{ $listing->created_at->format('d M') }}</span>
        </div>
    </div>
</div>
