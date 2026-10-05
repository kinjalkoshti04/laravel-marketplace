<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    private function register(array $overrides = [])
    {
        return $this->post('/register', array_merge([
            'name' => 'Asha Verma',
            'email' => 'asha@example.com',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ], $overrides));
    }

    public static function weakPasswords(): array
    {
        return [
            'too short' => ['S0me!x', 'at least 8 characters'],
            'no uppercase' => ['str0ng!pass', 'one uppercase and one lowercase letter'],
            'no lowercase' => ['STR0NG!PASS', 'one uppercase and one lowercase letter'],
            'no number' => ['Strong!Pass', 'at least one number'],
            'no symbol' => ['Str0ngPass1', 'at least one symbol'],
            'too long' => [str_repeat('Aa1!', 17), 'must not be greater than 64 characters'],
        ];
    }

    #[DataProvider('weakPasswords')]
    public function test_weak_passwords_are_rejected(string $password, string $expectedMessage): void
    {
        $this->register(['password' => $password, 'password_confirmation' => $password])
            ->assertSessionHasErrors('password');

        $this->assertStringContainsString($expectedMessage, implode(' ', session('errors')->get('password')));
        $this->assertGuest();
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->register(['password_confirmation' => 'Different1!'])
            ->assertSessionHasErrors(['password' => 'The two passwords do not match.']);
    }

    public function test_required_fields(): void
    {
        $this->post('/register', [])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_name_must_be_at_least_two_characters(): void
    {
        $this->register(['name' => 'A'])->assertSessionHasErrors('name');
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->register(['email' => 'not-an-email'])->assertSessionHasErrors('email');
    }

    public function test_duplicate_email_has_a_helpful_message(): void
    {
        User::factory()->create(['email' => 'asha@example.com']);

        $this->register(['email' => 'Asha@Example.com'])
            ->assertSessionHasErrors(['email' => 'An account with this email already exists. Try logging in instead.']);
    }

    public function test_email_is_stored_in_lowercase(): void
    {
        $this->register(['email' => '  Asha@Example.COM '])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'asha@example.com']);
        $this->assertAuthenticated();
    }

    public function test_register_and_login_forms_use_jquery_validation_instead_of_browser_popups(): void
    {
        foreach (['/register' => 'register', '/login' => 'login'] as $url => $name) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-validate="'.$name.'" novalidate', false)
                ->assertSee('auth-validation', false);
        }

        $this->get('/register')->assertSee(['Your password must have:', 'Suggest a strong password']);
    }
}
