<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categories and subcategories share one table:
 * parent_id = NULL  -> top-level category
 * parent_id = X     -> subcategory of X
 */
class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }
}
