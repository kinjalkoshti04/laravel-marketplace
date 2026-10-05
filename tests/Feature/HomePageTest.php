<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CategorySeeder::class, LocationSeeder::class]);
    }

    public function test_home_page_shows_categories_cities_and_active_listings(): void
    {
        $active = $this->makeListing('Royal Enfield Classic 350');
        $sold = $this->makeListing('Old Sold Scooter', Listing::STATUS_SOLD);

        $this->get('/')
            ->assertOk()
            ->assertSee('Vehicles')
            ->assertSee('Mumbai')
            ->assertSee($active->title)
            ->assertDontSee($sold->title);
    }

    public function test_home_page_works_without_listings(): void
    {
        $this->get('/')->assertOk()->assertSee('No listings yet');
    }

    private function makeListing(string $title, string $status = Listing::STATUS_ACTIVE): Listing
    {
        $sub = Category::where('slug', 'motorcycles')->firstOrFail();
        $city = City::where('slug', 'mumbai')->with('state')->firstOrFail();

        return Listing::create([
            'user_id' => User::factory()->create()->id,
            'category_id' => $sub->parent_id,
            'subcategory_id' => $sub->id,
            'country_id' => $city->state->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'area_id' => $city->areas()->first()->id,
            'title' => $title,
            'description' => 'Test listing',
            'price' => 150000,
            'status' => $status,
        ]);
    }
}
