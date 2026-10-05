@props(['listing'])

{{-- Listing card used on the home and browse pages. Expects ->withCardData() to be eager loaded. --}}
<a href="{{ route('listings.show', $listing) }}"
   class="group flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
    <div class="aspect-[4/3] overflow-hidden bg-gray-100">
        @if ($listing->primaryImage)
            <img src="{{ $listing->primaryImage->url }}" alt="{{ $listing->title }}" loading="lazy"
                 class="h-full w-full object-cover transition group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-sm text-gray-400">No photo</div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3">
        <p class="text-lg font-bold text-gray-900">{{ $listing->formatted_price }}</p>
        <h3 class="mt-1 line-clamp-2 text-sm text-gray-700">{{ $listing->title }}</h3>
        <p class="mt-1 text-xs text-indigo-600">{{ $listing->subcategory?->name }}</p>

        <div class="mt-auto flex items-center justify-between pt-3 text-xs text-gray-500">
            <span class="truncate">{{ $listing->location_label }}</span>
            <span class="shrink-0 ps-2">{{ $listing->created_at->diffForHumans(short: true) }}</span>
        </div>
    </div>
</a>
