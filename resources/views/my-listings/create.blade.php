<x-app-layout>
    <x-slot:title>Post an ad</x-slot:title>

    <x-slot name="header">
        <h4 class="mb-0">Post an ad</h4>
    </x-slot>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('my-listings.store') }}" enctype="multipart/form-data" class="card card-body" id="listing-form">
                    @csrf

                    @include('my-listings._form')

                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                        <a href="{{ route('my-listings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <x-primary-button>Post ad</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
