<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowseListingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CategorySeeder::class, LocationSeeder::class]);

        Listing::factory()->inSubcategory('cars')->inCity('mumbai')->create(['title' => 'Swift in Mumbai', 'price' => 500000]);
        Listing::factory()->inSubcategory('scooters')->inCity('mumbai')->create(['title' => 'Activa in Mumbai', 'price' => 60000]);
        Listing::factory()->inSubcategory('cars')->inCity('pune')->create(['title' => 'Creta in Pune', 'price' => 1400000]);
        Listing::factory()->inSubcategory('mobile-phones')->inCity('pune')->create(['title' => 'iPhone in Pune', 'price' => 40000]);
        Listing::factory()->inSubcategory('cars')->inCity('mumbai')->status(Listing::STATUS_SOLD)->create(['title' => 'Sold Honda City']);
    }

    public function test_category_page_lists_only_that_category(): void
    {
        $this->get('/category/vehicles')->assertOk()
            ->assertSee(['Swift in Mumbai', 'Activa in Mumbai', 'Creta in Pune'])
            ->assertDontSee(['iPhone in Pune', 'Sold Honda City']);
    }

    public function test_subcategory_page_lists_only_that_subcategory(): void
    {
        $this->get('/category/cars')->assertOk()
            ->assertSee(['Swift in Mumbai', 'Creta in Pune'])
            ->assertDontSee('Activa in Mumbai');
    }

    public function test_city_page_lists_only_that_city(): void
    {
        $this->get('/city/pune')->assertOk()
            ->assertSee(['Creta in Pune', 'iPhone in Pune'])
            ->assertDontSee(['Swift in Mumbai', 'Activa in Mumbai']);
    }

    public function test_city_and_category_page_combines_both_filters(): void
    {
        $this->get('/city/mumbai/vehicles')->assertOk()
            ->assertSee(['Vehicles in Mumbai', 'Swift in Mumbai', 'Activa in Mumbai'])
            ->assertDontSee(['Creta in Pune', 'iPhone in Pune']);

        $this->get('/city/mumbai/cars')->assertOk()
            ->assertSee('Swift in Mumbai')
            ->assertDontSee('Activa in Mumbai');
    }

    public function test_search_and_price_filters(): void
    {
        $this->get('/listings?q=Creta')->assertOk()
            ->assertSee('Creta in Pune')
            ->assertDontSee('Swift in Mumbai');

        $this->get('/category/vehicles?min_price=100000&max_price=600000')->assertOk()
            ->assertSee('Swift in Mumbai')
            ->assertDontSee(['Activa in Mumbai', 'Creta in Pune']);

        $this->get('/listings?city=pune')->assertOk()
            ->assertSee('iPhone in Pune')
            ->assertDontSee('Swift in Mumbai');
    }

    public function test_unknown_slugs_return_404(): void
    {
        $this->get('/category/does-not-exist')->assertNotFound();
        $this->get('/city/atlantis')->assertNotFound();
        $this->get('/listing/nope-123456')->assertNotFound();
    }

    public function test_detail_page_shows_listing_and_counts_views_once(): void
    {
        $listing = Listing::where('title', 'Swift in Mumbai')->firstOrFail();
        $listing->user->update(['phone' => '9876543210']);

        $this->get(route('listings.show', $listing))->assertOk()
            ->assertDontSee('9876543210')
            ->assertSee(['Swift in Mumbai', 'Cars', 'Mumbai', $listing->user->name, '₹ 5,00,000'])
            ->assertSee('Log in to see phone number');
        $this->get(route('listings.show', $listing))->assertOk();

        $this->assertSame(1, $listing->fresh()->views_count);
    }

    public function test_guest_returns_to_the_ad_with_phone_shown_after_login(): void
    {
        $listing = Listing::where('title', 'Swift in Mumbai')->firstOrFail();
        $listing->user->update(['phone' => '9876543210']);
        $buyer = User::factory()->create();

        $this->get(route('listings.contact', $listing))->assertRedirect(route('login'));

        $this->post('/login', ['email' => $buyer->email, 'password' => 'password'])
            ->assertRedirect(route('listings.contact', $listing));

        $this->get(route('listings.contact', $listing))
            ->assertRedirect(route('listings.show', $listing).'#seller');

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee('9876543210')
            ->assertSee('show: true', false);
    }

    public function test_sold_listing_is_viewable_but_marked(): void
    {
        $listing = Listing::where('title', 'Sold Honda City')->firstOrFail();

        $this->get(route('listings.show', $listing))->assertOk()->assertSee('This item has been sold.');
    }

    public function test_inactive_listing_is_only_visible_to_owner(): void
    {
        $listing = Listing::factory()->status(Listing::STATUS_INACTIVE)->create();

        $this->get(route('listings.show', $listing))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('listings.show', $listing))->assertNotFound();
        $this->actingAs($listing->user)->get(route('listings.show', $listing))->assertOk()->assertSee('Edit your ad');
    }
}
