<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Category;
use App\Models\Country;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * The logged-in user's own listings (/dashboard/listings).
 */
class MyListingController extends Controller
{
    private const STATUSES = [Listing::STATUS_ACTIVE, Listing::STATUS_SOLD, Listing::STATUS_INACTIVE];

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $base = $request->user()->listings();

        $listings = (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->withCardData()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('my-listings.index', compact('listings', 'status', 'counts'));
    }

    public function create(): View
    {
        return view('my-listings.create', $this->formData(new Listing([
            'type' => 'product',
            // Pre-select the country when there is only one.
            'country_id' => Country::count() === 1 ? Country::value('id') : null,
        ])));
    }

    public function store(StoreListingRequest $request): RedirectResponse
    {
        $listing = DB::transaction(function () use ($request) {
            $listing = $request->user()->listings()->create(
                Arr::except($request->validated(), ['images'])
            );
            $this->storeImages($listing, $request->file('images', []));

            return $listing;
        });

        return redirect()->route('listings.show', $listing)->with('status', 'Your ad has been posted.');
    }

    public function edit(Listing $listing): View
    {
        Gate::authorize('update', $listing);

        return view('my-listings.edit', $this->formData($listing->load('images')));
    }

    public function update(UpdateListingRequest $request, Listing $listing): RedirectResponse
    {
        $removedPaths = DB::transaction(function () use ($request, $listing) {
            $listing->update(Arr::except($request->validated(), ['images', 'remove_images']));

            $removed = $listing->images()->whereIn('id', $request->validated('remove_images', []))->get();
            $listing->images()->whereKey($removed->modelKeys())->delete();

            // If the cover was removed, a remaining older photo takes over before new uploads are added.
            $this->ensurePrimaryImage($listing);
            $this->storeImages($listing, $request->file('images', []));

            return $removed->pluck('path')->all();
        });

        // Delete files only after the database changes are committed.
        Storage::disk('public')->delete($removedPaths);

        return redirect()->route('my-listings.index')->with('status', 'Listing updated.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        Gate::authorize('delete', $listing);

        // Soft delete: images are kept so the listing could be restored.
        $listing->delete();

        return redirect()->route('my-listings.index')->with('status', 'Listing deleted.');
    }

    private function formData(Listing $listing): array
    {
        return [
            'listing' => $listing,
            'categories' => Category::topLevel()->active()->get(['id', 'name']),
            'countries' => Country::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  UploadedFile[]  $files
     */
    private function storeImages(Listing $listing, array $files): void
    {
        $hasPrimary = $listing->images()->where('is_primary', true)->exists();
        $order = (int) $listing->images()->max('sort_order');

        foreach ($files as $file) {
            $listing->images()->create([
                'path' => $file->store("listings/{$listing->id}", 'public'),
                'sort_order' => ++$order,
                'is_primary' => ! $hasPrimary,
            ]);
            $hasPrimary = true;
        }
    }

    private function ensurePrimaryImage(Listing $listing): void
    {
        if (! $listing->images()->where('is_primary', true)->exists()) {
            $listing->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }
    }
}
