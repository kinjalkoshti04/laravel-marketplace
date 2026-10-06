<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function show(Listing $listing): View
    {
        // Hidden listings look like they don't exist to everyone but the owner.
        abort_unless(Gate::allows('view', $listing), 404);

        $listing->load([
            'images', 'user:id,name,phone,created_at',
            'category:id,name,slug', 'subcategory:id,name,slug',
            'country:id,name', 'state:id,name', 'city:id,name,slug', 'area:id,name',
        ]);

        $similar = Listing::active()
            ->where('subcategory_id', $listing->subcategory_id)
            ->whereKeyNot($listing->id)
            ->withCardData()
            // Same city first, then newest.
            ->orderByRaw('city_id = ? desc', [$listing->city_id])
            ->latest()
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'similar'));
    }

    public function contact(Listing $listing): RedirectResponse
    {
        return redirect()->route('listings.show', $listing)
            ->with('reveal_phone', true)
            ->withFragment('seller');
    }
}
