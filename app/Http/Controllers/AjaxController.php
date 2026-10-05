<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints for the dependent dropdowns on the listing form.
 * Each returns [{id, name}, ...] for the given parent id.
 */
class AjaxController extends Controller
{
    public function states(Request $request): JsonResponse
    {
        return $this->options(State::where('country_id', $request->integer('country_id'))->orderBy('name'));
    }

    public function cities(Request $request): JsonResponse
    {
        return $this->options(City::where('state_id', $request->integer('state_id'))->orderBy('name'));
    }

    public function areas(Request $request): JsonResponse
    {
        return $this->options(Area::where('city_id', $request->integer('city_id'))->orderBy('name'));
    }

    public function subcategories(Request $request): JsonResponse
    {
        return $this->options(
            Category::active()->where('parent_id', $request->integer('category_id'))->orderBy('sort_order')
        );
    }

    private function options($query): JsonResponse
    {
        return response()->json($query->get(['id', 'name']));
    }
}
