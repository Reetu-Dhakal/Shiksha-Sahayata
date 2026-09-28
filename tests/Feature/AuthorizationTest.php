<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('role:admin')->get('/__test/admin-only', fn () => response('admin-ok'));
        Route::middleware('role:school_officer')->get('/__test/school-only', fn () => response('school-ok'));
    }

    public function test_guest_cannot_pass_role_middleware(): void
    {
        $this->get('/__test/admin-only')->assertUnauthorized();
    }

    public function test_admin_passes_admin_role_middleware(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get('/__test/admin-only')->assertOk()->assertSee('admin-ok');
    }

    public function test_student_is_forbidden_from_admin_route(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)->get('/__test/admin-only')->assertForbidden();
    }

    public function test_committee_member_is_forbidden_from_admin_route(): void
    {
        $user = User::factory()->committee()->create();

        $this->actingAs($user)->get('/__test/admin-only')->assertForbidden();
    }

    public function test_school_officer_passes_school_role_middleware(): void
    {
        $user = User::factory()->schoolOfficer()->create();

        $this->actingAs($user)->get('/__test/school-only')->assertOk();
    }

    public function test_local_officer_is_forbidden_from_school_route(): void
    {
        $user = User::factory()->localOfficer()->create();

        $this->actingAs($user)->get('/__test/school-only')->assertForbidden();
    }

    public function test_inactive_user_is_logged_out_by_role_middleware(): void
    {
        $user = User::factory()->admin()->inactive()->create();

        $this->actingAs($user)->get('/__test/admin-only')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_role_labels_are_available_for_interface(): void
    {
        $this->assertSame('Administrator', Role::ADMIN->label());
        $this->assertSame('Selection Committee Member', Role::COMMITTEE->label());
        $this->assertTrue(Role::STUDENT->isApplicant());
        $this->assertFalse(Role::ADMIN->isApplicant());
    }

    public function test_has_role_accepts_enum_and_string_values(): void
    {
        $student = User::factory()->create(['role' => Role::STUDENT]);

        $this->assertTrue($student->hasRole(Role::STUDENT));
        $this->assertTrue($student->hasRole('student'));
        $this->assertTrue($student->hasRole(Role::ADMIN, Role::STUDENT));
        $this->assertFalse($student->hasRole(Role::ADMIN));
        $this->assertFalse($student->hasRole('admin'));
    }
}
