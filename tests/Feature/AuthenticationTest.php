<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_phone(): void
    {
        $user = User::factory()->create([
            'email' => null,
            'phone' => '9800000011',
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'login' => '9800000011',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first()
        );
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_registering_creates_an_active_applicant_account(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'student',
            'name' => 'Aasha Student',
            'email' => 'aasha@example.com',
            'phone' => '9800000022',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'aasha@example.com')->firstOrFail();
        $this->assertSame(Role::STUDENT, $user->role);
        $this->assertTrue($user->isActive());
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_register_requires_email_or_phone(): void
    {
        $this->post('/register', [
            'account_type' => 'student',
            'name' => 'No Contact',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors(['email', 'phone']);

        $this->assertGuest();
    }
}
