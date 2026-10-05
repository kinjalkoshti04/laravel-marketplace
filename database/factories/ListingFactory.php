<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Picks a random existing subcategory and area, so CategorySeeder and
 * LocationSeeder must have run first.
 *
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $subcategory = Category::whereNotNull('parent_id')->inRandomOrder()->firstOrFail();
        $area = Area::with('city.state')->inRandomOrder()->firstOrFail();

        return [
            'user_id' => User::factory(),
            ...$this->categoryAttributes($subcategory),
            ...$this->locationAttributes($area),
            'type' => 'product',
            'title' => ucfirst(fake()->words(4, true)),
            'description' => fake()->paragraph(3),
            'price' => fake()->numberBetween(500, 100000),
            'is_negotiable' => fake()->boolean(),
            'status' => Listing::STATUS_ACTIVE,
        ];
    }

    public function inSubcategory(string $slug): static
    {
        return $this->state(fn () => $this->categoryAttributes(Category::where('slug', $slug)->firstOrFail()));
    }

    public function inCity(string $slug): static
    {
        return $this->state(fn () => $this->locationAttributes(
            City::where('slug', $slug)->firstOrFail()->areas()->with('city.state')->firstOrFail()
        ));
    }

    public function status(string $status): static
    {
        return $this->state(['status' => $status]);
    }

    private function categoryAttributes(Category $subcategory): array
    {
        return ['category_id' => $subcategory->parent_id, 'subcategory_id' => $subcategory->id];
    }

    private function locationAttributes(Area $area): array
    {
        return [
            'country_id' => $area->city->state->country_id,
            'state_id' => $area->city->state_id,
            'city_id' => $area->city_id,
            'area_id' => $area->id,
        ];
    }
}
