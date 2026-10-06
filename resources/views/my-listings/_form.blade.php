{{-- Shared create/edit form. Expects $listing, $categories, $countries. --}}
@php
    $selected = [
        'category' => old('category_id', $listing->category_id),
        'subcategory' => old('subcategory_id', $listing->subcategory_id),
        'country' => old('country_id', $listing->country_id),
        'state' => old('state_id', $listing->state_id),
        'city' => old('city_id', $listing->city_id),
        'area' => old('area_id', $listing->area_id),
    ];
    $urls = [
        'subcategories' => route('ajax.subcategories'),
        'states' => route('ajax.states'),
        'cities' => route('ajax.cities'),
        'areas' => route('ajax.areas'),
    ];
@endphp

<div x-data="listingForm({ urls: @js($urls), selected: @js($selected) })">

    @if ($errors->any())
        <div class="alert alert-danger">
            Please fix the errors below.
            <strong>If you selected photos, please choose them again</strong> &ndash; browsers clear the photo field when a form has errors.
        </div>
    @endif

    <h5 class="mb-3">Ad details</h5>

    <div class="mb-3">
        <label class="form-label d-block">Type</label>
        @foreach (\App\Models\Listing::TYPES as $type)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_{{ $type }}" value="{{ $type }}"
                       @checked(old('type', $listing->type) === $type)>
                <label class="form-check-label" for="type_{{ $type }}">{{ ucfirst($type) }}</label>
            </div>
        @endforeach
        <x-input-error :messages="$errors->get('type')" />
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" name="category_id" class="form-select" x-model="category" @change="categoryChanged()" required>
                <option value="">Select category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected($selected['category'] == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" />
        </div>

        <div class="col-md-6 mb-3">
            <label for="subcategory_id" class="form-label">Subcategory</label>
            <select id="subcategory_id" name="subcategory_id" class="form-select" x-model="subcategory"
                    :disabled="!category || loading.subcategories" required>
                <option value="" x-text="loading.subcategories ? 'Loading...' : 'Select subcategory'"></option>
                <template x-for="o in subcategories" :key="o.id">
                    <option :value="o.id" x-text="o.name" :selected="o.id == subcategory"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('subcategory_id')" />
        </div>
    </div>

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <x-text-input id="title" name="title" maxlength="120" required
                      :value="old('title', $listing->title)" placeholder="e.g. iPhone 13 128GB, excellent condition" />
        <x-input-error :messages="$errors->get('title')" />
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Details</label>
        <textarea id="description" name="description" rows="5" maxlength="5000" required class="form-control"
                  placeholder="Condition, age, features, reason for selling...">{{ old('description', $listing->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <div class="row align-items-end">
        <div class="col-md-6 mb-3">
            <label for="price" class="form-label">Price (₹)</label>
            <x-text-input id="price" name="price" type="number" min="0" step="0.01" required
                          :value="old('price', $listing->price !== null ? (float) $listing->price : null)" />
            <x-input-error :messages="$errors->get('price')" />
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_negotiable" id="is_negotiable" value="1"
                       @checked(old('is_negotiable', $listing->is_negotiable))>
                <label class="form-check-label" for="is_negotiable">Price is negotiable</label>
            </div>
        </div>
    </div>

    <h5 class="mt-2 mb-3">Location</h5>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="country_id" class="form-label">Country</label>
            <select id="country_id" name="country_id" class="form-select" x-model="country" @change="countryChanged()" required>
                <option value="">Select country</option>
                @foreach ($countries as $c)
                    <option value="{{ $c->id }}" @selected($selected['country'] == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('country_id')" />
        </div>

        @foreach ([
            ['state', 'states', 'State', 'stateChanged()', 'country'],
            ['city', 'cities', 'City', 'cityChanged()', 'state'],
            ['area', 'areas', 'Area', null, 'city'],
        ] as [$field, $list, $label, $onChange, $parent])
            <div class="col-md-6 mb-3">
                <label for="{{ $field }}_id" class="form-label">{{ $label }}</label>
                <select id="{{ $field }}_id" name="{{ $field }}_id" class="form-select" x-model="{{ $field }}"
                        @if ($onChange) @change="{{ $onChange }}" @endif
                        :disabled="!{{ $parent }} || loading.{{ $list }}" required>
                    <option value="" x-text="loading.{{ $list }} ? 'Loading...' : 'Select {{ strtolower($label) }}'"></option>
                    <template x-for="o in {{ $list }}" :key="o.id">
                        <option :value="o.id" x-text="o.name" :selected="o.id == {{ $field }}"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get($field.'_id')" />
            </div>
        @endforeach
    </div>

    <h5 class="mt-2 mb-3">Photos</h5>

    @if ($listing->exists && $listing->images->isNotEmpty())
        <p class="small text-muted mb-2">Current photos. Tick the ones you want to remove.</p>
        <div class="row g-2 mb-3" id="current-photos">
            @foreach ($listing->images as $image)
                <div class="col-4 col-md-3">
                    <div class="border rounded p-1 bg-white">
                        <img src="{{ $image->url }}" alt="" class="w-100 rounded" style="height: 90px; object-fit: cover;">
                        <div class="form-check small mt-1">
                            <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_{{ $image->id }}"
                                   @checked(in_array($image->id, old('remove_images', [])))>
                            <label class="form-check-label" for="remove_{{ $image->id }}">
                                Remove @if ($image->is_primary) <span class="badge bg-primary">Cover</span> @endif
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mb-3">
        <label for="images" class="form-label">{{ $listing->exists ? 'Add photos' : 'Upload photos' }}</label>
        <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
               class="form-control @if ($errors->any()) border-warning @endif" @change="previewImages($event)">
        @if ($errors->any())
            <div class="form-text text-warning-emphasis fw-semibold" id="reselect-photos">Please select your photos again before saving.</div>
        @endif
        <div class="form-text">Up to {{ \App\Http\Requests\StoreListingRequest::MAX_IMAGES }} photos in total, JPG/PNG/WebP, max 4 MB each. The first photo is the cover.</div>
        <x-input-error :messages="$errors->get('images')" />
        @foreach ($errors->get('images.*') as $messages)
            <x-input-error :messages="$messages" />
        @endforeach

        <div class="row g-2 mt-1" x-show="previews.length" x-cloak>
            <template x-for="src in previews" :key="src">
                <div class="col-4 col-md-3">
                    <img :src="src" alt="" class="w-100 rounded border" style="height: 90px; object-fit: cover;">
                </div>
            </template>
        </div>
    </div>

    @if ($listing->exists)
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select w-auto">
                @foreach (['active' => 'Active (visible to everyone)', 'sold' => 'Sold', 'inactive' => 'Inactive (hidden)'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $listing->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    @endif
</div>
