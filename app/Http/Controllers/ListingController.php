<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function show(Request $request, Listing $listing): View
    {
        // Hidden listings look like they don't exist to everyone but the owner.
        abort_unless(Gate::allows('view', $listing), 404);

        $listing->load([
            'images', 'user:id,name,phone,created_at',
            'category:id,name,slug', 'subcategory:id,name,slug',
            'country:id,name', 'state:id,name', 'city:id,name,slug', 'area:id,name',
        ]);

        $this->countView($request, $listing);

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

    /**
     * Counts one view per visitor session; the owner's own visits are not counted.
     */
    private function countView(Request $request, Listing $listing): void
    {
        $viewed = $request->session()->get('viewed_listings', []);

        if ($request->user()?->id === $listing->user_id || in_array($listing->id, $viewed, true)) {
            return;
        }

        // Query builder increment so updated_at is not touched.
        DB::table('listings')->where('id', $listing->id)->increment('views_count');
        $listing->views_count++;
        $request->session()->push('viewed_listings', $listing->id);
    }
}
