<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Public browse pages. All four routes share one query and one view:
 *   /listings                      all listings (+ ?q, ?city, ?category)
 *   /category/{category}           category or subcategory
 *   /city/{city}                   city
 *   /city/{city}/{category}        city + category/subcategory
 */
class BrowseController extends Controller
{
    public const SORTS = [
        'newest' => 'Newest first',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
    ];

    public function index(Request $request): View
    {
        $city = $request->filled('city') ? City::where('slug', $request->query('city'))->first() : null;
        $category = $request->filled('category') ? Category::where('slug', $request->query('category'))->first() : null;

        return $this->render($request, $city, $category);
    }

    public function category(Request $request, Category $category): View
    {
        return $this->render($request, null, $category);
    }

    public function city(Request $request, City $city): View
    {
        return $this->render($request, $city, null);
    }

    public function cityCategory(Request $request, City $city, Category $category): View
    {
        return $this->render($request, $city, $category);
    }

    private function render(Request $request, ?City $city, ?Category $category): View
    {
        abort_if($category && ! $category->is_active, 404);
        $category?->load('parent');

        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'newest';

        $listings = $this->baseQuery($city, $category)
            ->search($request->query('q'))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', (float) $request->query('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', (float) $request->query('max_price')))
            ->withCardData()
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('browse.index', [
            'listings' => $listings,
            'city' => $city,
            'category' => $category,
            'sort' => $sort,
            'title' => $this->title($city, $category, $request->query('q')),
            'categoryFacets' => $this->categoryFacets($city, $category),
            'cityFacets' => $this->cityFacets($city, $category),
        ]);
    }

    private function baseQuery(?City $city, ?Category $category)
    {
        return Listing::active()->inCity($city)->inCategory($category);
    }

    /**
     * Next category level to drill into, with active listing counts in the current city.
     * No category -> top-level categories; top-level -> its subcategories; subcategory -> siblings.
     *
     * @return Collection<int, array{category: Category, count: int}>
     */
    private function categoryFacets(?City $city, ?Category $category): Collection
    {
        $parent = $category?->isSubcategory() ? $category->parent : $category;
        $column = $parent ? 'subcategory_id' : 'category_id';

        $counts = $this->baseQuery($city, $parent)
            ->selectRaw("{$column} as cat_id, count(*) as total")
            ->groupBy($column)
            ->pluck('total', 'cat_id');

        $options = $parent ? $parent->children : Category::topLevel()->active()->get();

        return $options->map(fn (Category $c) => ['category' => $c, 'count' => (int) ($counts[$c->id] ?? 0)])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();
    }

    /**
     * Cities with active listings in the current category (only shown when no city is chosen).
     *
     * @return Collection<int, array{city: City, count: int}>
     */
    private function cityFacets(?City $city, ?Category $category): Collection
    {
        if ($city) {
            return collect();
        }

        $counts = $this->baseQuery(null, $category)
            ->selectRaw('city_id, count(*) as total')
            ->groupBy('city_id')
            ->pluck('total', 'city_id');

        return City::whereIn('id', $counts->keys())->orderBy('name')->get()
            ->map(fn (City $c) => ['city' => $c, 'count' => (int) $counts[$c->id]])
            ->sortByDesc('count')
            ->values();
    }

    private function title(?City $city, ?Category $category, ?string $q): string
    {
        $title = match (true) {
            $city && $category => "{$category->name} in {$city->name}",
            (bool) $category => $category->name,
            (bool) $city => "Listings in {$city->name}",
            default => 'All listings',
        };

        return filled($q) ? "Results for \"{$q}\" · {$title}" : $title;
    }
}
