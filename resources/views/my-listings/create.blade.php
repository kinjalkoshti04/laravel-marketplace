<x-app-layout>
    <x-slot:title>Post an ad</x-slot:title>

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Post an ad</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('my-listings.store') }}" enctype="multipart/form-data"
                  class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf

                @include('my-listings._form')

                <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                    <a href="{{ route('my-listings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <x-primary-button>Post ad</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
