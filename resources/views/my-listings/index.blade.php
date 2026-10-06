<x-app-layout>
    <x-slot:title>My listings</x-slot:title>

    <x-slot name="header">
        <h4 class="mb-0">My listings</h4>
    </x-slot>

    <div class="container">
        <x-flash-status />

        <ul class="nav nav-tabs mb-3" id="status-tabs">
            @foreach (['' => 'All', 'active' => 'Active', 'sold' => 'Sold', 'inactive' => 'Inactive'] as $value => $label)
                @php $count = $value ? ($counts[$value] ?? 0) : $counts->sum(); @endphp
                <li class="nav-item">
                    <a class="nav-link @if ($status == $value) active @endif"
                       href="{{ route('my-listings.index', array_filter(['status' => $value])) }}">{{ $label }} ({{ $count }})</a>
                </li>
            @endforeach
        </ul>

        @if ($listings->isEmpty())
            <div class="alert alert-info">
                {{ $status ? "You have no {$status} listings." : "You haven't posted any ads yet." }}
                <a href="{{ route('my-listings.create') }}">Post an ad</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered bg-white align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">Photo</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listings as $listing)
                            <tr>
                                <td>
                                    @if ($listing->primaryImage)
                                        <img src="{{ $listing->primaryImage->url }}" alt="" class="rounded" style="width: 90px; height: 65px; object-fit: cover;">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('listings.show', $listing) }}">{{ $listing->title }}</a>
                                    <div class="small text-muted">{{ $listing->subcategory?->name }} &middot; {{ $listing->location_label }}</div>
                                </td>
                                <td class="text-nowrap">{{ $listing->formatted_price }}</td>
                                <td><x-status-badge :status="$listing->status" /></td>
                                <td class="text-nowrap small">{{ $listing->created_at->format('d M Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('my-listings.edit', $listing) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('my-listings.destroy', $listing) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this listing?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $listings->links() }}
        @endif
    </div>
</x-app-layout>
