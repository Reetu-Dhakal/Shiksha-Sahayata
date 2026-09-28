<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_renders(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    #[DataProvider('roleDashboardProvider')]
    public function test_each_role_can_render_its_dashboard(string $role): void
    {
        $user = User::factory()->create(['role' => Role::from($role)]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function roleDashboardProvider(): array
    {
        return [
            'student' => [Role::STUDENT->value],
            'guardian' => [Role::GUARDIAN->value],
            'school officer' => [Role::SCHOOL_OFFICER->value],
            'local officer' => [Role::LOCAL_OFFICER->value],
            'committee' => [Role::COMMITTEE->value],
            'admin' => [Role::ADMIN->value],
        ];
    }

    public function test_locale_can_be_switched(): void
    {
        $this->post('/locale/np')->assertRedirect();

        $this->withSession(['locale' => 'np'])
            ->get('/')
            ->assertOk()
            ->assertSee('शिक्षा सहायता');
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->post('/locale/xx')->assertNotFound();
    }
}
