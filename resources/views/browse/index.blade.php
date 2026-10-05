{{-- Shared by /listings, /category/{slug}, /city/{slug} and /city/{city}/{category}. --}}
@php
    // Builds the pretty URL for a city/category combination, keeping the search term.
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

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{-- Breadcrumbs --}}
        <nav class="mb-3 flex flex-wrap items-center gap-1 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
            @if ($city)
                <span>/</span><a href="{{ $browseUrl($city, null) }}" class="hover:text-indigo-600">{{ $city->name }}</a>
            @endif
            @if ($topCategory)
                <span>/</span><a href="{{ $browseUrl($city, $topCategory) }}" class="hover:text-indigo-600">{{ $topCategory->name }}</a>
            @endif
            @if ($category?->isSubcategory())
                <span>/</span><span class="text-gray-700">{{ $category->name }}</span>
            @endif
        </nav>

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                <p class="text-sm text-gray-500">{{ $listings->total() }} {{ Str::plural('ad', $listings->total()) }} found</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-4">
            {{-- Sidebar: filters + facets --}}
            <aside class="space-y-6 lg:col-span-1">
                <form method="GET" class="space-y-3 rounded-lg border border-gray-200 bg-white p-4">
                    <div>
                        <label for="q" class="text-sm font-medium text-gray-700">Search</label>
                        <input id="q" name="q" value="{{ request('q') }}" placeholder="e.g. iPhone, sofa"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Price (₹)</span>
                        <div class="mt-1 flex gap-2">
                            <input name="min_price" type="number" min="0" value="{{ request('min_price') }}" placeholder="Min"
                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input name="max_price" type="number" min="0" value="{{ request('max_price') }}" placeholder="Max"
                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label for="sort" class="text-sm font-medium text-gray-700">Sort by</label>
                        <select id="sort" name="sort"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Http\Controllers\BrowseController::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Keep the ?city / ?category filters on the generic /listings page --}}
                    @if (request()->routeIs('listings.index'))
                        @foreach (['city', 'category'] as $keep)
                            @if (request($keep))
                                <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
                            @endif
                        @endforeach
                    @endif
                    <div class="flex gap-2">
                        <button class="flex-1 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply</button>
                        <a href="{{ url()->current() }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">Reset</a>
                    </div>
                </form>

                @if ($categoryFacets->isNotEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <h2 class="mb-2 text-sm font-semibold text-gray-900">{{ $topCategory ? $topCategory->name : 'Categories' }}</h2>
                        <ul class="space-y-1 text-sm">
                            @foreach ($categoryFacets as ['category' => $facet, 'count' => $count])
                                <li>
                                    <a href="{{ $browseUrl($city, $facet) }}"
                                       @class(['flex justify-between rounded px-2 py-1 hover:bg-gray-50',
                                               'bg-indigo-50 font-semibold text-indigo-700' => $category?->is($facet),
                                               'text-gray-700' => ! $category?->is($facet)])>
                                        <span>{{ $facet->icon }} {{ $facet->name }}</span>
                                        <span class="text-gray-400">{{ $count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($cityFacets->isNotEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <h2 class="mb-2 text-sm font-semibold text-gray-900">Cities</h2>
                        <ul class="space-y-1 text-sm">
                            @foreach ($cityFacets as ['city' => $facet, 'count' => $count])
                                <li>
                                    <a href="{{ $browseUrl($facet, $category) }}" class="flex justify-between rounded px-2 py-1 text-gray-700 hover:bg-gray-50">
                                        <span>{{ $facet->name }}</span>
                                        <span class="text-gray-400">{{ $count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>

            {{-- Results --}}
            <section class="lg:col-span-3">
                @if ($listings->isEmpty())
                    <div class="rounded-lg bg-white p-10 text-center shadow-sm">
                        <p class="text-gray-600">No ads match your filters.</p>
                        <a href="{{ route('listings.index') }}" class="mt-3 inline-block text-sm font-semibold text-indigo-600 hover:underline">See all listings</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        @foreach ($listings as $listing)
                            <x-listing-card :listing="$listing" />
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $listings->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
