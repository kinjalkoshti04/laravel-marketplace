<x-app-layout>
    <x-slot:title>{{ $listing->title }} in {{ $listing->city->name }}</x-slot:title>

    <div class="container">
        <x-flash-status />

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('browse.city', $listing->city) }}">{{ $listing->city->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('browse.city-category', [$listing->city, $listing->category]) }}">{{ $listing->category->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('browse.city-category', [$listing->city, $listing->subcategory]) }}">{{ $listing->subcategory->name }}</a></li>
            </ol>
        </nav>

        @if ($listing->status !== 'active')
            <div class="alert alert-warning">
                {{ $listing->status === 'sold' ? 'This item has been sold.' : 'This ad is inactive and only visible to you.' }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                {{-- Photos --}}
                <div class="card mb-4" x-data="{ active: 0 }" id="gallery">
                    @if ($listing->images->isEmpty())
                        <div class="gallery-main d-flex align-items-center justify-content-center text-muted">No photos</div>
                    @else
                        @foreach ($listing->images as $i => $image)
                            <img src="{{ $image->url }}" alt="{{ $listing->title }} photo {{ $i + 1 }}"
                                 class="card-img-top gallery-main" x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif>
                        @endforeach

                        @if ($listing->images->count() > 1)
                            <div class="card-body d-flex gap-2 overflow-auto">
                                @foreach ($listing->images as $i => $image)
                                    <img src="{{ $image->url }}" alt="Photo {{ $i + 1 }}" class="gallery-thumb rounded border"
                                         :class="active === {{ $i }} ? 'border-primary border-2' : 'opacity-75'"
                                         @click="active = {{ $i }}">
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

                <div class="card mb-4">
                    <div class="card-header fw-semibold">Details</div>
                    <div class="card-body">
                        <table class="table table-sm mb-4">
                            <tbody>
                                <tr><th class="w-25">Type</th><td>{{ ucfirst($listing->type) }}</td></tr>
                                <tr><th>Category</th><td>{{ $listing->category->name }}</td></tr>
                                <tr><th>Subcategory</th><td>{{ $listing->subcategory->name }}</td></tr>
                                <tr><th>Area</th><td>{{ $listing->area->name }}</td></tr>
                                <tr><th>City</th><td>{{ $listing->city->name }}</td></tr>
                                <tr><th>State</th><td>{{ $listing->state->name }}</td></tr>
                            </tbody>
                        </table>

                        <h6>Description</h6>
                        <p id="description" class="mb-0" style="white-space: pre-line;">{{ $listing->description }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="fw-bold mb-1">{{ $listing->formatted_price }}</h3>
                        @if ($listing->is_negotiable)
                            <span class="badge bg-success">Negotiable</span>
                        @endif
                        <h1 class="h5 mt-2">{{ $listing->title }}</h1>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>{{ $listing->area->name }}, {{ $listing->city->name }}</span>
                            <span>{{ $listing->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="small text-muted mt-1">{{ number_format($listing->views_count) }} views &middot; Ad ID {{ $listing->id }}</div>
                    </div>
                </div>

                <div class="card mb-3" id="seller">
                    <div class="card-header fw-semibold">Seller</div>
                    <div class="card-body">
                        <p class="mb-0 fw-semibold">{{ $listing->user->name }}</p>
                        <p class="small text-muted">Member since {{ $listing->user->created_at->format('M Y') }}</p>

                        @can('update', $listing)
                            <a href="{{ route('my-listings.edit', $listing) }}" class="btn btn-primary w-100">Edit your ad</a>
                        @else
                            @if ($listing->user->phone)
                                @auth
                                    <div x-data="{ show: @js((bool) session('reveal_phone')) }">
                                        <button type="button" class="btn btn-primary w-100" x-show="!show" @click="show = true">Show phone number</button>
                                        <a x-show="show" x-cloak href="tel:{{ $listing->user->phone }}" class="btn btn-outline-primary w-100">{{ $listing->user->phone }}</a>
                                    </div>
                                @else
                                    <a href="{{ route('listings.contact', $listing) }}" class="btn btn-primary w-100">Log in to see phone number</a>
                                @endauth
                            @endif
                        @endcan
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header fw-semibold">Posted in</div>
                    <div class="card-body small">
                        {{ $listing->area->name }}, {{ $listing->city->name }}, {{ $listing->state->name }}, {{ $listing->country->name }}
                        <div class="mt-2"><a href="{{ route('browse.city', $listing->city) }}">More ads in {{ $listing->city->name }}</a></div>
                    </div>
                </div>
            </div>
        </div>

        @if ($similar->isNotEmpty())
            <h5 class="mt-2 mb-3">Similar ads</h5>
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($similar as $item)
                    <div class="col">
                        <x-listing-card :listing="$item" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
