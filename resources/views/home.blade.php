<x-app-layout>
    {{-- Hero --}}
    <section class="bg-indigo-700">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Buy and sell anything near you</h1>
            <p class="mt-2 text-indigo-100">Mobiles, vehicles, property, jobs, services and more across India.</p>
        </div>
    </section>

    <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Categories --}}
        <section>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Browse categories</h2>
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-5 lg:grid-cols-9">
                @foreach ($categories as $category)
                    <a href="{{ url('/category/'.$category->slug) }}"
                       class="flex flex-col items-center rounded-lg border border-gray-200 bg-white p-3 text-center transition hover:border-indigo-400 hover:shadow-sm">
                        <span class="text-3xl">{{ $category->icon }}</span>
                        <span class="mt-2 text-xs font-medium text-gray-700">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Popular cities --}}
        <section>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Popular cities</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($popularCities as $city)
                    <a href="{{ url('/city/'.$city->slug) }}"
                       class="rounded-full border border-gray-300 bg-white px-4 py-1.5 text-sm text-gray-700 transition hover:border-indigo-500 hover:text-indigo-600">
                        {{ $city->name }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Latest listings --}}
        <section>
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Fresh recommendations</h2>

            @if ($latestListings->isEmpty())
                <p class="rounded-lg bg-white p-8 text-center text-gray-500">No listings yet. Be the first to post an ad!</p>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($latestListings as $listing)
                        <x-listing-card :listing="$listing" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
