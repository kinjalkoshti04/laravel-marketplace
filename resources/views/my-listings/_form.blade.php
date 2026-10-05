{{-- Shared create/edit form. Expects $listing, $categories, $countries. --}}
@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100';
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

<div x-data="listingForm({ urls: @js($urls), selected: @js($selected) })" class="space-y-8">

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
            Please fix the highlighted fields below.
        </div>
    @endif

    {{-- What --}}
    <section class="space-y-5">
        <h3 class="text-lg font-semibold text-gray-900">What are you offering?</h3>

        <div>
            <x-input-label value="Type" />
            <div class="mt-2 flex gap-6">
                @foreach (\App\Models\Listing::TYPES as $type)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="type" value="{{ $type }}" class="text-indigo-600 focus:ring-indigo-500"
                               @checked(old('type', $listing->type) === $type)>
                        {{ ucfirst($type) }}
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="category_id" value="Category" />
                <select id="category_id" name="category_id" x-model="category" @change="categoryChanged()" class="{{ $selectClass }}" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($selected['category'] == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="subcategory_id" value="Subcategory" />
                <select id="subcategory_id" name="subcategory_id" x-model="subcategory" class="{{ $selectClass }}"
                        :disabled="!category || loading.subcategories" required>
                    <option value="" x-text="loading.subcategories ? 'Loading…' : 'Select subcategory'"></option>
                    <template x-for="o in subcategories" :key="o.id">
                        <option :value="o.id" x-text="o.name" :selected="o.id == subcategory"></option>
                    </template>
                </select>
                <x-input-error :messages="$errors->get('subcategory_id')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="title" value="Title" />
            <x-text-input id="title" name="title" class="mt-1 block w-full" maxlength="120" required
                          :value="old('title', $listing->title)" placeholder="e.g. iPhone 13 128GB, excellent condition" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Details" />
            <textarea id="description" name="description" rows="6" maxlength="5000" required class="{{ $selectClass }}"
                      placeholder="Condition, age, features, reason for selling…">{{ old('description', $listing->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid items-end gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="price" value="Price (₹)" />
                <x-text-input id="price" name="price" type="number" min="0" step="0.01" class="mt-1 block w-full" required
                              :value="old('price', $listing->price !== null ? (float) $listing->price : null)" />
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>
            <label class="inline-flex items-center gap-2 pb-2 text-sm text-gray-700">
                <input type="checkbox" name="is_negotiable" value="1" class="rounded text-indigo-600 focus:ring-indigo-500"
                       @checked(old('is_negotiable', $listing->is_negotiable))>
                Price is negotiable
            </label>
        </div>
    </section>

    {{-- Where --}}
    <section class="space-y-5 border-t border-gray-200 pt-6">
        <h3 class="text-lg font-semibold text-gray-900">Location</h3>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="country_id" value="Country" />
                <select id="country_id" name="country_id" x-model="country" @change="countryChanged()" class="{{ $selectClass }}" required>
                    <option value="">Select country</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}" @selected($selected['country'] == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
            </div>

            @foreach ([
                ['state', 'states', 'State', 'stateChanged()', 'country'],
                ['city', 'cities', 'City', 'cityChanged()', 'state'],
                ['area', 'areas', 'Area', null, 'city'],
            ] as [$field, $list, $label, $onChange, $parent])
                <div>
                    <x-input-label :for="$field.'_id'" :value="$label" />
                    <select id="{{ $field }}_id" name="{{ $field }}_id" x-model="{{ $field }}"
                            @if ($onChange) @change="{{ $onChange }}" @endif
                            :disabled="!{{ $parent }} || loading.{{ $list }}" class="{{ $selectClass }}" required>
                        <option value="" x-text="loading.{{ $list }} ? 'Loading…' : 'Select {{ strtolower($label) }}'"></option>
                        <template x-for="o in {{ $list }}" :key="o.id">
                            <option :value="o.id" x-text="o.name" :selected="o.id == {{ $field }}"></option>
                        </template>
                    </select>
                    <x-input-error :messages="$errors->get($field.'_id')" class="mt-2" />
                </div>
            @endforeach
        </div>
    </section>

    {{-- Photos --}}
    <section class="space-y-4 border-t border-gray-200 pt-6">
        <h3 class="text-lg font-semibold text-gray-900">Photos</h3>

        @if ($listing->exists && $listing->images->isNotEmpty())
            <div>
                <p class="mb-2 text-sm text-gray-600">Current photos. Tick the ones you want to remove.</p>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach ($listing->images as $image)
                        <label class="relative block cursor-pointer overflow-hidden rounded-md border border-gray-200">
                            <img src="{{ $image->url }}" alt="" class="aspect-square w-full object-cover">
                            <span class="absolute inset-x-0 bottom-0 flex items-center gap-1 bg-white/90 px-2 py-1 text-xs text-gray-700">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="rounded text-red-600 focus:ring-red-500"
                                       @checked(in_array($image->id, old('remove_images', [])))>
                                Remove
                            </span>
                            @if ($image->is_primary)
                                <span class="absolute left-1 top-1 rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Cover</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <x-input-label for="images" :value="$listing->exists ? 'Add photos' : 'Upload photos'" />
            <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                   @change="previewImages($event)"
                   class="mt-1 block w-full text-sm text-gray-600 file:me-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
            <p class="mt-1 text-xs text-gray-500">Up to {{ \App\Http\Requests\StoreListingRequest::MAX_IMAGES }} photos in total, JPG/PNG/WebP, max 4 MB each. The first photo is the cover.</p>
            <x-input-error :messages="$errors->get('images')" class="mt-2" />
            @foreach ($errors->get('images.*') as $messages)
                <x-input-error :messages="$messages" class="mt-1" />
            @endforeach

            <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-5" x-show="previews.length" x-cloak>
                <template x-for="src in previews" :key="src">
                    <img :src="src" alt="" class="aspect-square w-full rounded-md border border-gray-200 object-cover">
                </template>
            </div>
        </div>
    </section>

    @if ($listing->exists)
        <section class="border-t border-gray-200 pt-6">
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="{{ $selectClass }} sm:w-64">
                @foreach (['active' => 'Active (visible to everyone)', 'sold' => 'Sold', 'inactive' => 'Inactive (hidden)'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $listing->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </section>
    @endif
</div>
