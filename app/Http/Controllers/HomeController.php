<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::topLevel()->active()->get();
        $popularCities = City::popular()->get();
        $allCities = City::orderBy('name')->get(['name', 'slug']);

        $latestListings = Listing::active()
            ->withCardData()
            ->latest()
            ->take(16)
            ->get();

        return view('home', compact('categories', 'popularCities', 'allCities', 'latestListings'));
    }
}
