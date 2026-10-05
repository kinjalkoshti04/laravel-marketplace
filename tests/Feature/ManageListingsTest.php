<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManageListingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CategorySeeder::class, LocationSeeder::class]);
        Storage::fake('public');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('my-listings.create'))->assertRedirect(route('login'));
        $this->post(route('my-listings.store'), $this->validData())->assertRedirect(route('login'));
    }

    public function test_user_can_post_a_listing_with_photos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('my-listings.store'), $this->validData([
            'images' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.png')],
        ]));

        $listing = Listing::firstOrFail();
        $response->assertRedirect(route('listings.show', $listing));

        $this->assertSame($user->id, $listing->user_id);
        $this->assertSame('Honda Activa 6G, single owner', $listing->title);
        $this->assertSame(Listing::STATUS_ACTIVE, $listing->status);
        $this->assertTrue($listing->is_negotiable);

        $this->assertCount(2, $listing->images);
        $this->assertTrue($listing->images->first()->is_primary);
        $this->assertFalse($listing->images->last()->is_primary);
        Storage::disk('public')->assertExists($listing->images->pluck('path')->all());
    }

    public function test_subcategory_must_belong_to_the_chosen_category(): void
    {
        $data = $this->validData(['subcategory_id' => Category::where('slug', 'mobile-phones')->value('id')]);

        $this->actingAs(User::factory()->create())
            ->post(route('my-listings.store'), $data)
            ->assertSessionHasErrors('subcategory_id');

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_area_must_belong_to_the_chosen_city(): void
    {
        $puneArea = City::where('slug', 'pune')->firstOrFail()->areas()->first();

        $this->actingAs(User::factory()->create())
            ->post(route('my-listings.store'), $this->validData(['area_id' => $puneArea->id]))
            ->assertSessionHasErrors('area_id');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('my-listings.store'), [])
            ->assertSessionHasErrors(['type', 'title', 'description', 'price', 'category_id', 'subcategory_id', 'country_id', 'state_id', 'city_id', 'area_id']);
    }

    public function test_owner_can_update_listing_and_remove_photos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('my-listings.store'), $this->validData([
            'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]));
        $listing = Listing::firstOrFail();
        [$first, $second] = $listing->images->all();

        $this->actingAs($user)
            ->put(route('my-listings.update', $listing), $this->validData([
                'title' => 'Honda Activa 6G, price dropped',
                'price' => 60000,
                'status' => Listing::STATUS_SOLD,
                'remove_images' => [$first->id],
            ]))
            ->assertRedirect(route('my-listings.index'));

        $listing->refresh();
        $this->assertSame('Honda Activa 6G, price dropped', $listing->title);
        $this->assertSame(Listing::STATUS_SOLD, $listing->status);
        $this->assertSame([$second->id], $listing->images->pluck('id')->all());
        $this->assertTrue($listing->images->first()->is_primary, 'Remaining photo becomes the cover.');
        Storage::disk('public')->assertMissing($first->path);
    }

    public function test_photo_limit_counts_existing_photos(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->for($user)->create();
        foreach (range(1, 4) as $i) {
            $listing->images()->create(['path' => "x/{$i}.jpg", 'sort_order' => $i]);
        }

        $this->actingAs($user)
            ->put(route('my-listings.update', $listing), $this->validData([
                'status' => 'active',
                'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
            ]))
            ->assertSessionHasErrors('images');
    }

    public function test_other_users_cannot_edit_update_or_delete(): void
    {
        $listing = Listing::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('my-listings.edit', $listing))->assertForbidden();
        $this->actingAs($intruder)->put(route('my-listings.update', $listing), $this->validData(['status' => 'active']))->assertForbidden();
        $this->actingAs($intruder)->delete(route('my-listings.destroy', $listing))->assertForbidden();

        $this->assertNotSoftDeleted($listing);
    }

    public function test_owner_can_delete_listing(): void
    {
        $listing = Listing::factory()->create();

        $this->actingAs($listing->user)
            ->delete(route('my-listings.destroy', $listing))
            ->assertRedirect(route('my-listings.index'));

        $this->assertSoftDeleted($listing);
    }

    public function test_my_listings_only_shows_own_listings(): void
    {
        $user = User::factory()->create();
        Listing::factory()->for($user)->create(['title' => 'Mine to sell']);
        Listing::factory()->create(['title' => 'Someone else ad']);

        $this->actingAs($user)->get(route('my-listings.index'))
            ->assertOk()
            ->assertSee('Mine to sell')
            ->assertDontSee('Someone else ad');
    }

    public function test_ajax_endpoints_return_children_of_the_parent(): void
    {
        $mumbai = City::where('slug', 'mumbai')->firstOrFail();
        $vehicles = Category::where('slug', 'vehicles')->firstOrFail();

        $this->getJson(route('ajax.areas', ['city_id' => $mumbai->id]))
            ->assertOk()
            ->assertJsonCount($mumbai->areas()->count())
            ->assertJsonFragment(['name' => 'Andheri']);

        $this->getJson(route('ajax.subcategories', ['category_id' => $vehicles->id]))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Scooters'])
            ->assertJsonMissing(['name' => 'Mobile Phones']);

        $this->getJson(route('ajax.cities', ['state_id' => 0]))->assertOk()->assertExactJson([]);
    }

    private function validData(array $overrides = []): array
    {
        $scooters = Category::where('slug', 'scooters')->firstOrFail();
        $andheri = Area::where('slug', 'andheri')->with('city.state')->firstOrFail();

        return array_merge([
            'type' => 'product',
            'title' => 'Honda Activa 6G, single owner',
            'description' => 'Well maintained scooter, 7,000 km driven, all papers clear.',
            'price' => 65000,
            'is_negotiable' => '1',
            'category_id' => $scooters->parent_id,
            'subcategory_id' => $scooters->id,
            'country_id' => $andheri->city->state->country_id,
            'state_id' => $andheri->city->state_id,
            'city_id' => $andheri->city_id,
            'area_id' => $andheri->id,
        ], $overrides);
    }
}
