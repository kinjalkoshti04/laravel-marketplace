<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My listings</h2>
            <a href="{{ route('my-listings.create') }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">+ Post an ad</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <x-flash-status />

            {{-- Status tabs --}}
            <nav class="mb-6 flex gap-2 overflow-x-auto text-sm">
                @foreach ([null => 'All', 'active' => 'Active', 'sold' => 'Sold', 'inactive' => 'Inactive'] as $value => $label)
                    @php $count = $value ? ($counts[$value] ?? 0) : $counts->sum(); @endphp
                    <a href="{{ route('my-listings.index', array_filter(['status' => $value])) }}"
                       @class([
                           'whitespace-nowrap rounded-full px-4 py-1.5 font-medium',
                           'bg-indigo-600 text-white' => $status == $value,
                           'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' => $status != $value,
                       ])>
                        {{ $label }} <span class="opacity-75">({{ $count }})</span>
                    </a>
                @endforeach
            </nav>

            @forelse ($listings as $listing)
                <div class="mb-3 flex gap-4 rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                    <a href="{{ route('listings.show', $listing) }}" class="h-24 w-32 shrink-0 overflow-hidden rounded-md bg-gray-100">
                        @if ($listing->primaryImage)
                            <img src="{{ $listing->primaryImage->url }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('listings.show', $listing) }}" class="truncate font-medium text-gray-900 hover:text-indigo-600">{{ $listing->title }}</a>
                            <x-status-badge :status="$listing->status" class="shrink-0" />
                        </div>
                        <p class="mt-1 font-bold text-gray-900">{{ $listing->formatted_price }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $listing->subcategory?->name }} · {{ $listing->location_label }} ·
                            {{ $listing->views_count }} views · posted {{ $listing->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-col items-end justify-center gap-2 text-sm">
                        <a href="{{ route('my-listings.edit', $listing) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('my-listings.destroy', $listing) }}"
                              onsubmit="return confirm('Delete this listing?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-lg bg-white p-10 text-center shadow-sm">
                    <p class="text-gray-600">{{ $status ? "You have no {$status} listings." : "You haven't posted any ads yet." }}</p>
                    <a href="{{ route('my-listings.create') }}" class="mt-4 inline-block font-semibold text-indigo-600 hover:underline">Post your first ad</a>
                </div>
            @endforelse

            <div class="mt-6">{{ $listings->links() }}</div>
        </div>
    </div>
</x-app-layout>
