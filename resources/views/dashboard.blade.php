<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
            <a href="{{ route('my-listings.create') }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">+ Post an ad</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <p class="text-gray-700">Welcome back, <span class="font-semibold">{{ Auth::user()->name }}</span>.</p>

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($stats as $label => $value)
                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <section>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Your latest ads</h3>
                    <a href="{{ route('my-listings.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">Manage all</a>
                </div>

                @if ($recent->isEmpty())
                    <div class="rounded-lg bg-white p-8 text-center text-gray-600 shadow-sm">
                        You haven't posted any ads yet.
                        <a href="{{ route('my-listings.create') }}" class="font-semibold text-indigo-600 hover:underline">Post your first ad</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        @foreach ($recent as $listing)
                            <x-listing-card :listing="$listing" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
