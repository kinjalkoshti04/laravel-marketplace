<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public const MAX_IMAGES = 5;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_negotiable' => $this->boolean('is_negotiable')]);
    }

    /**
     * Every child id must belong to the parent chosen above it, so a tampered
     * form cannot store e.g. a Pune area under a Mumbai city.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Listing::TYPES)],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'is_negotiable' => ['boolean'],

            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')
                ->whereNull('parent_id')->where('is_active', true)],
            'subcategory_id' => ['required', 'integer', Rule::exists('categories', 'id')
                ->where('parent_id', $this->integer('category_id'))->where('is_active', true)],

            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')
                ->where('country_id', $this->integer('country_id'))],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')
                ->where('state_id', $this->integer('state_id'))],
            'area_id' => ['required', 'integer', Rule::exists('areas', 'id')
                ->where('city_id', $this->integer('city_id'))],

            'images' => ['nullable', 'array', 'max:'.self::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'subcategory_id.exists' => 'Please choose a subcategory of the selected category.',
            'state_id.exists' => 'Please choose a state in the selected country.',
            'city_id.exists' => 'Please choose a city in the selected state.',
            'area_id.exists' => 'Please choose an area in the selected city.',
            'images.max' => 'You can upload at most :max photos.',
            'images.*.max' => 'Each photo must be 4 MB or smaller.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'subcategory_id' => 'subcategory',
            'country_id' => 'country',
            'state_id' => 'state',
            'city_id' => 'city',
            'area_id' => 'area',
            'images.*' => 'photo',
        ];
    }
}
