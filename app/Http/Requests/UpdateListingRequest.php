<?php

namespace App\Http\Requests;

use App\Models\Listing;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateListingRequest extends StoreListingRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('listing')) ?? false;
    }

    public function rules(): array
    {
        $listing = $this->route('listing');

        return [
            ...parent::rules(),
            'status' => ['required', Rule::in([Listing::STATUS_ACTIVE, Listing::STATUS_SOLD, Listing::STATUS_INACTIVE])],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('listing_images', 'id')->where('listing_id', $listing->id)],
        ];
    }

    /**
     * Existing photos that are kept plus new uploads must stay within the limit.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $listing = $this->route('listing');
                $kept = $listing->images()->whereNotIn('id', (array) $this->input('remove_images', []))->count();
                $total = $kept + count($this->file('images', []));

                if ($total > self::MAX_IMAGES) {
                    $validator->errors()->add('images', 'A listing can have at most '.self::MAX_IMAGES." photos (you would have {$total}).");
                }
            },
        ];
    }
}
