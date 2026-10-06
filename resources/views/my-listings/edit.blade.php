<x-app-layout>
    <x-slot:title>Edit {{ $listing->title }}</x-slot:title>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit ad</h4>
            <a href="{{ route('listings.show', $listing) }}" class="small">View ad</a>
        </div>
    </x-slot>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('my-listings.update', $listing) }}" enctype="multipart/form-data" class="card card-body" id="listing-form">
                    @csrf
                    @method('PUT')

                    @include('my-listings._form')

                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                        <a href="{{ route('my-listings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <x-primary-button>Save changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
