<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['state_id', 'name', 'slug', 'is_popular'];

    protected function casts(): array
    {
        return ['is_popular' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class)->orderBy('name');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function scopePopular(Builder $query): void
    {
        $query->where('is_popular', true)->orderBy('name');
    }
}
