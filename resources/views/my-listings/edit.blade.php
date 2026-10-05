<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit ad</h2>
            <a href="{{ route('listings.show', $listing) }}" class="text-sm text-indigo-600 hover:underline">View ad</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('my-listings.update', $listing) }}" enctype="multipart/form-data"
                  class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                @method('PUT')

                @include('my-listings._form')

                <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                    <a href="{{ route('my-listings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <x-primary-button>Save changes</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
