<x-app-layout>
    <x-slot:title>{{ $listing->title }} in {{ $listing->city->name }}</x-slot:title>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <x-flash-status />

        {{-- Breadcrumbs: every level links to its browse page --}}
        <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
            <span>/</span><a href="{{ route('browse.city', $listing->city) }}" class="hover:text-indigo-600">{{ $listing->city->name }}</a>
            <span>/</span><a href="{{ route('browse.city-category', [$listing->city, $listing->category]) }}" class="hover:text-indigo-600">{{ $listing->category->name }}</a>
            <span>/</span><a href="{{ route('browse.city-category', [$listing->city, $listing->subcategory]) }}" class="hover:text-indigo-600">{{ $listing->subcategory->name }}</a>
        </nav>

        @if ($listing->status !== 'active')
            <div class="mb-4 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ $listing->status === 'sold' ? 'This item has been sold.' : 'This ad is inactive and only visible to you.' }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Gallery + details --}}
            <div class="space-y-6 lg:col-span-2">
                <div x-data="{ active: 0 }" class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    @if ($listing->images->isEmpty())
                        <div class="flex aspect-[4/3] items-center justify-center bg-gray-100 text-gray-400">No photos</div>
                    @else
                        <div class="aspect-[4/3] bg-gray-900">
                            @foreach ($listing->images as $i => $image)
                                <img src="{{ $image->url }}" alt="{{ $listing->title }} photo {{ $i + 1 }}"
                                     x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                                     class="h-full w-full object-contain">
                            @endforeach
                        </div>
                        @if ($listing->images->count() > 1)
                            <div class="flex gap-2 overflow-x-auto p-3">
                                @foreach ($listing->images as $i => $image)
                                    <button type="button" @click="active = {{ $i }}"
                                            :class="active === {{ $i }} ? 'ring-2 ring-indigo-500' : 'opacity-70 hover:opacity-100'"
                                            class="h-16 w-20 shrink-0 overflow-hidden rounded-md">
                                        <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Details</h2>
                    <dl class="mb-6 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                        @foreach ([
                            'Type' => ucfirst($listing->type),
                            'Category' => $listing->category->name,
                            'Subcategory' => $listing->subcategory->name,
                            'Area' => $listing->area->name,
                            'City' => $listing->city->name,
                            'State' => $listing->state->name,
                        ] as $label => $value)
                            <div>
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <h2 class="mb-2 text-lg font-semibold text-gray-900">Description</h2>
                    <p class="whitespace-pre-line text-gray-700">{{ $listing->description }}</p>
                </div>
            </div>

            {{-- Price, seller, location --}}
            <aside class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-3xl font-bold text-gray-900">{{ $listing->formatted_price }}</p>
                    @if ($listing->is_negotiable)
                        <span class="mt-1 inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Negotiable</span>
                    @endif
                    <h1 class="mt-3 text-xl font-semibold text-gray-800">{{ $listing->title }}</h1>
                    <div class="mt-4 flex justify-between text-sm text-gray-500">
                        <span>{{ $listing->area->name }}, {{ $listing->city->name }}</span>
                        <span>{{ $listing->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">{{ number_format($listing->views_count) }} views · Ad ID {{ $listing->id }}</p>
                </div>

                <div id="seller" class="rounded-lg border border-gray-200 bg-white p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Seller</h2>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-lg font-bold text-indigo-700">
                            {{ Str::upper(Str::substr($listing->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $listing->user->name }}</p>
                            <p class="text-xs text-gray-500">Member since {{ $listing->user->created_at->format('M Y') }}</p>
                        </div>
                    </div>

                    @can('update', $listing)
                        <a href="{{ route('my-listings.edit', $listing) }}"
                           class="mt-4 block rounded-md bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">Edit your ad</a>
                    @else
                        @if ($listing->user->phone)
                            @auth
                                <div x-data="{ show: @js((bool) session('reveal_phone')) }" class="mt-4">
                                    <button type="button" x-show="!show" @click="show = true"
                                            class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Show phone number</button>
                                    <a x-show="show" x-cloak href="tel:{{ $listing->user->phone }}"
                                       class="block rounded-md border border-indigo-600 px-4 py-2 text-center text-sm font-semibold text-indigo-700">📞 {{ $listing->user->phone }}</a>
                                </div>
                            @else
                                <a href="{{ route('listings.contact', $listing) }}"
                                   class="mt-4 block rounded-md bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">Log in to see phone number</a>
                            @endauth
                        @endif
                    @endcan
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm">
                    <h2 class="mb-2 font-semibold uppercase tracking-wide text-gray-500">Posted in</h2>
                    <p class="text-gray-700">{{ $listing->area->name }}, {{ $listing->city->name }}, {{ $listing->state->name }}, {{ $listing->country->name }}</p>
                    <a href="{{ route('browse.city', $listing->city) }}" class="mt-2 inline-block text-indigo-600 hover:underline">More ads in {{ $listing->city->name }}</a>
                </div>
            </aside>
        </div>

        @if ($similar->isNotEmpty())
            <section class="mt-10">
                <h2 class="mb-4 text-xl font-semibold text-gray-900">Similar ads</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-listing-card :listing="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
