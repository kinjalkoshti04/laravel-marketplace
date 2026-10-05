<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_can_be_given_when_registering(): void
    {
        $this->post('/register', [
            'name' => 'Asha Verma',
            'email' => 'asha@example.com',
            'phone' => '+91 98765-43210',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame('+91 98765-43210', User::where('email', 'asha@example.com')->value('phone'));
    }

    public function test_phone_is_optional_but_must_look_like_a_phone_number(): void
    {
        $data = ['name' => 'Asha', 'email' => 'asha@example.com', 'password' => 'Str0ng!Pass', 'password_confirmation' => 'Str0ng!Pass'];

        $this->post('/register', [...$data, 'phone' => 'call me'])->assertSessionHasErrors('phone');
        $this->assertGuest();

        $this->post('/register', $data)->assertSessionHasNoErrors();
        $this->assertNull(User::first()->phone);
    }

    public function test_phone_can_be_updated_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'phone' => '9822012345'])
            ->assertSessionHasNoErrors();

        $this->assertSame('9822012345', $user->fresh()->phone);
    }
}
