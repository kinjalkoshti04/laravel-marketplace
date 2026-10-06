<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CategorySeeder::class, LocationSeeder::class]);
    }

    public function test_every_form_uses_jquery_validation_instead_of_browser_popups(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->for($user)->create();

        $guestPages = [
            '/' => ['search'],
            '/listings' => ['filters'],
            '/login' => ['login'],
            '/register' => ['register'],
            '/forgot-password' => ['forgot-password'],
            '/reset-password/some-token' => ['reset-password'],
        ];
        foreach ($guestPages as $url => $forms) {
            $response = $this->get($url)->assertOk();
            foreach ($forms as $form) {
                $response->assertSee('data-validate="'.$form.'" novalidate', false);
            }
        }

        $userPages = [
            route('my-listings.create') => ['listing'],
            route('my-listings.edit', $listing) => ['listing'],
            '/profile' => ['profile', 'update-password', 'delete-account'],
            '/confirm-password' => ['confirm-password'],
        ];
        foreach ($userPages as $url => $forms) {
            $response = $this->actingAs($user)->get($url)->assertOk();
            foreach ($forms as $form) {
                $response->assertSee('data-validate="'.$form.'" novalidate', false);
            }
        }
    }

    public function test_password_forms_show_the_strong_password_checklist(): void
    {
        $this->get('/reset-password/some-token')->assertSee(['Your password must have:', 'Suggest a strong password']);
        $this->actingAs(User::factory()->create())->get('/profile')->assertSee(['Your password must have:', 'Suggest a strong password']);
    }

    /* ---------------------------------------------------------------- browse filters (server side) */

    public function test_valid_filters_are_applied(): void
    {
        Listing::factory()->create(['title' => 'Cheap phone', 'price' => 5000]);
        Listing::factory()->create(['title' => 'Costly phone', 'price' => 90000]);

        $this->get('/listings?min_price=1000&max_price=10000&sort=price_asc')
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertSee('Cheap phone')
            ->assertDontSee('Costly phone');
    }

    public function test_invalid_filters_are_rejected_with_messages(): void
    {
        $this->get('/category/vehicles?min_price=abc')
            ->assertRedirect(url('/category/vehicles'))
            ->assertSessionHasErrors(['min_price' => 'Min price must be a number.']);

        $this->get('/listings?min_price=-5')->assertSessionHasErrors('min_price');
        $this->get('/listings?sort=hacker')->assertSessionHasErrors('sort');
        $this->get('/listings?q='.str_repeat('a', 101))->assertSessionHasErrors('q');

        $this->get('/listings?min_price=5000&max_price=100')
            ->assertSessionHasErrors(['max_price' => 'Max price must be greater than or equal to min price.']);
    }

    public function test_filter_errors_are_shown_on_the_page(): void
    {
        $this->followingRedirects()
            ->get('/listings?min_price=5000&max_price=100')
            ->assertOk()
            ->assertSee('Some filters were invalid and have been ignored.')
            ->assertSee('Max price must be greater than or equal to min price.');
    }

    /* ---------------------------------------------------------------- profile (server side) */

    public function test_profile_rules_match_registration(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)->patch('/profile', ['name' => 'A', 'email' => $user->email])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)->patch('/profile', ['name' => 'Asha', 'email' => 'Taken@Example.com'])
            ->assertSessionHasErrors(['email' => 'This email is already used by another account.']);

        $this->actingAs($user)->patch('/profile', ['name' => 'Asha', 'email' => '  New.Mail@Example.COM '])
            ->assertSessionHasNoErrors();
        $this->assertSame('new.mail@example.com', $user->fresh()->email);
    }
}
