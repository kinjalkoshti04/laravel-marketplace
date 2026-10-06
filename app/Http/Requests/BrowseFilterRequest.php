<?php

namespace App\Http\Requests;

use App\Http\Controllers\BrowseController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Search / filter values on the browse pages (query string).
 */
class BrowseFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'max_price' => [
                'nullable', 'numeric', 'min:0', 'max:9999999999',
                Rule::when($this->filled('min_price') && is_numeric($this->query('min_price')), ['gte:min_price']),
            ],
            'sort' => ['nullable', Rule::in(array_keys(BrowseController::SORTS))],
            'city' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.max' => 'Search text may not be longer than 100 characters.',
            'min_price.numeric' => 'Min price must be a number.',
            'max_price.numeric' => 'Max price must be a number.',
            'min_price.min' => 'Min price cannot be negative.',
            'max_price.min' => 'Max price cannot be negative.',
            'max_price.gte' => 'Max price must be greater than or equal to min price.',
        ];
    }

    /**
     * On error, reload the same page without the invalid filters.
     */
    protected function getRedirectUrl(): string
    {
        return $this->url();
    }
}
