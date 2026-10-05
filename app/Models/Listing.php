<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Listing extends Model
{
    /** @use HasFactory<\Database\Factories\ListingFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SOLD = 'sold';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPES = ['product', 'service'];

    protected $fillable = [
        'user_id', 'category_id', 'subcategory_id',
        'country_id', 'state_id', 'city_id', 'area_id',
        'type', 'title', 'description', 'price', 'is_negotiable', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            $listing->slug ??= static::makeSlug($listing->title);
        });
    }

    public static function makeSlug(string $title): string
    {
        // Random suffix keeps slugs unique without an extra lookup query.
        return Str::limit(Str::slug($title), 80, '').'-'.Str::lower(Str::random(6));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ----------------------------------------------------------------
     | Relationships
     | ---------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ListingImage::class)->ofMany(
            ['is_primary' => 'max', 'id' => 'min']
        );
    }

    /* ----------------------------------------------------------------
     | Scopes
     | ---------------------------------------------------------------- */

    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Works for both a top-level category and a subcategory.
     */
    public function scopeInCategory(Builder $query, ?Category $category): void
    {
        if (! $category) {
            return;
        }

        $column = $category->isSubcategory() ? 'subcategory_id' : 'category_id';
        $query->where($column, $category->id);
    }

    public function scopeInCity(Builder $query, ?City $city): void
    {
        if ($city) {
            $query->where('city_id', $city->id);
        }
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term !== '') {
            $query->where(fn (Builder $q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"));
        }
    }

    /**
     * Eager-loads everything a listing card needs (avoids N+1 queries).
     */
    public function scopeWithCardData(Builder $query): void
    {
        $query->with(['primaryImage', 'city:id,name,slug', 'area:id,name', 'subcategory:id,name,slug']);
    }

    /* ----------------------------------------------------------------
     | Accessors
     | ---------------------------------------------------------------- */

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => (float) $this->price == 0.0
            ? 'Free'
            : '₹ '.self::indianNumber((float) $this->price));
    }

    /**
     * Indian digit grouping (12,34,567) without needing the intl extension.
     */
    public static function indianNumber(float $amount): string
    {
        $decimals = $amount == floor($amount) ? 0 : 2;
        [$whole, $fraction] = array_pad(explode('.', number_format($amount, $decimals, '.', '')), 2, null);

        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        $grouped = $rest === '' ? $lastThree : preg_replace('/\B(?=(\d{2})+$)/', ',', $rest).','.$lastThree;

        return $grouped.($fraction !== null ? '.'.$fraction : '');
    }

    protected function locationLabel(): Attribute
    {
        return Attribute::get(fn () => collect([$this->area?->name, $this->city?->name])->filter()->implode(', '));
    }
}
