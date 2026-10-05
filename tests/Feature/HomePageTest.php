<?php

namespace Tests\Feature;

use App\Models\Listing;
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
        $active = Listing::factory()->create(['title' => 'Royal Enfield Classic 350']);
        $sold = Listing::factory()->status(Listing::STATUS_SOLD)->create(['title' => 'Old Sold Scooter']);

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
}
