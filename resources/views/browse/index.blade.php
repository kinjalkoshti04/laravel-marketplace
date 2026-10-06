{{-- Shared by /listings, /category/{slug}, /city/{slug} and /city/{city}/{category}. --}}
@php
    // Builds the URL for a city/category combination, keeping the search term.
    $browseUrl = function ($city, $category) {
        $query = array_filter(['q' => request('q')]);

        return match (true) {
            $city && $category => route('browse.city-category', [$city, $category, ...$query]),
            (bool) $category => route('browse.category', [$category, ...$query]),
            (bool) $city => route('browse.city', [$city, ...$query]),
            default => route('listings.index', $query),
        };
    };
    $topCategory = $category?->isSubcategory() ? $category->parent : $category;
@endphp

<x-app-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                @if ($city)
                    <li class="breadcrumb-item"><a href="{{ $browseUrl($city, null) }}">{{ $city->name }}</a></li>
                @endif
                @if ($topCategory)
                    <li class="breadcrumb-item"><a href="{{ $browseUrl($city, $topCategory) }}">{{ $topCategory->name }}</a></li>
                @endif
                @if ($category?->isSubcategory())
                    <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                @endif
            </ol>
        </nav>

        <h4 class="mb-0">{{ $title }}</h4>
        <p id="result-count" class="text-muted small">{{ $listings->total() }} {{ Str::plural('ad', $listings->total()) }} found</p>

        <div class="row">
            {{-- Filters --}}
            <div class="col-lg-3 mb-4">
                <form method="GET" class="card card-body mb-3" id="filters">
                    <div class="mb-2">
                        <label for="q" class="form-label small mb-1">Search</label>
                        <input id="q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="e.g. iPhone, sofa">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Price (₹)</label>
                        <div class="input-group input-group-sm">
                            <input name="min_price" type="number" min="0" value="{{ request('min_price') }}" class="form-control" placeholder="Min">
                            <input name="max_price" type="number" min="0" value="{{ request('max_price') }}" class="form-control" placeholder="Max">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="sort" class="form-label small mb-1">Sort by</label>
                        <select id="sort" name="sort" class="form-select form-select-sm">
                            @foreach (\App\Http\Controllers\BrowseController::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Keep the ?city / ?category filters on the /listings page --}}
                    @if (request()->routeIs('listings.index'))
                        @foreach (['city', 'category'] as $keep)
                            @if (request($keep))
                                <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
                            @endif
                        @endforeach
                    @endif
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill">Apply</button>
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>

                @if ($categoryFacets->isNotEmpty())
                    <div class="card mb-3" id="category-facets">
                        <div class="card-header small fw-semibold">{{ $topCategory ? $topCategory->name : 'Categories' }}</div>
                        <div class="list-group list-group-flush small">
                            @foreach ($categoryFacets as ['category' => $facet, 'count' => $count])
                                <a href="{{ $browseUrl($city, $facet) }}"
                                   class="list-group-item list-group-item-action d-flex justify-content-between @if ($category?->is($facet)) active @endif">
                                    {{ $facet->name }}
                                    <span>{{ $count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($cityFacets->isNotEmpty())
                    <div class="card" id="city-facets">
                        <div class="card-header small fw-semibold">Cities</div>
                        <div class="list-group list-group-flush small">
                            @foreach ($cityFacets as ['city' => $facet, 'count' => $count])
                                <a href="{{ $browseUrl($facet, $category) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                                    {{ $facet->name }}
                                    <span>{{ $count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Results --}}
            <div class="col-lg-9">
                @if ($listings->isEmpty())
                    <div class="alert alert-info">
                        No ads match your filters. <a href="{{ route('listings.index') }}">See all listings</a>
                    </div>
                @else
                    <div class="row row-cols-2 row-cols-md-3 g-3">
                        @foreach ($listings as $listing)
                            <div class="col">
                                <x-listing-card :listing="$listing" />
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $listings->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
