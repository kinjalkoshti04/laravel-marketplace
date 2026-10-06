<x-app-layout>
    <x-slot:title>Buy and sell near you</x-slot:title>

    <div class="container">
        {{-- Search --}}
        <form method="GET" action="{{ route('listings.index') }}" class="card card-body mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="city" class="form-select" aria-label="City">
                        <option value="">All cities</option>
                        @foreach ($allCities as $c)
                            <option value="{{ $c->slug }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <input name="q" class="form-control" placeholder="Search cars, mobiles, jobs and more..." aria-label="Search">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <div class="row">
            {{-- Categories + cities --}}
            <div class="col-lg-3 mb-4">
                <div class="card mb-4">
                    <div class="card-header fw-semibold">Categories</div>
                    <div class="list-group list-group-flush">
                        @foreach ($categories as $category)
                            <a href="{{ route('browse.category', $category) }}" class="list-group-item list-group-item-action">{{ $category->name }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header fw-semibold">Popular cities</div>
                    <div class="list-group list-group-flush">
                        @foreach ($popularCities as $city)
                            <a href="{{ route('browse.city', $city) }}" class="list-group-item list-group-item-action">{{ $city->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Latest ads --}}
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Latest ads</h5>
                    <a href="{{ route('listings.index') }}" class="small">View all</a>
                </div>

                @if ($latestListings->isEmpty())
                    <div class="alert alert-info">No listings yet. Be the first to post an ad!</div>
                @else
                    <div class="row row-cols-2 row-cols-md-3 g-3">
                        @foreach ($latestListings as $listing)
                            <div class="col">
                                <x-listing-card :listing="$listing" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
