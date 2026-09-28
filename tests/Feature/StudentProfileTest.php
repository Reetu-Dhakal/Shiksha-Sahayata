<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validProfileData(): array
    {
        return [
            'name' => 'Test Student',
            'name_np' => 'परीक्षण विद्यार्थी',
            'date_of_birth' => '2011-05-02',
            'gender' => 'FEMALE',
            'education_level' => 'SECONDARY',
            'grade' => 9,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'student_category' => 'LOW_INCOME',
            'birth_registration_number' => 'BR-2055-999',
            'school_id' => 1,
            'guardian_name' => 'Test Guardian',
            'guardian_relationship' => 'Father',
            'guardian_phone' => '9800000000',
            'guardian_citizenship_number' => null,
            'guardian_address' => 'Ward 1, Dhulikhel',
        ];
    }

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-TEST'],
            [
                'name' => 'Test School',
                'province' => 'Bagmati Province',
                'district' => 'Kavrepalanchok',
                'municipality' => 'Dhulikhel Municipality',
                'status' => School::STATUS_ACTIVE,
            ]
        );
    }

    public function test_student_without_profile_is_redirected_to_create(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('profile.create'));
    }

    public function test_student_can_create_profile_and_gets_unique_scholar_student_id(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);
        $school = $this->school();

        $response = $this->actingAs($user)->post('/profile', $this->validProfileData() + [
            'school_id' => $school->id,
        ]);

        $response->assertRedirect(route('profile.show'));

        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('SS-'.now()->year.'-0001', $student->scholar_student_id);
        $this->assertSame(Student::STATUS_UNVERIFIED, $student->verification_status);
        $this->assertNotNull($student->guardian_id);

        $second = User::factory()->create(['role' => Role::STUDENT]);
        $this->actingAs($second)->post('/profile', $this->validProfileData() + [
            'school_id' => $school->id,
        ]);

        $this->assertSame(
            'SS-'.now()->year.'-0002',
            Student::query()->where('user_id', $second->id)->firstOrFail()->scholar_student_id
        );
    }

    public function test_profile_requires_required_fields(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)
            ->post('/profile', ['name' => 'Incomplete'])
            ->assertSessionHasErrors([
                'date_of_birth', 'gender', 'education_level', 'grade',
                'province', 'district', 'municipality', 'school_id',
                'guardian_name', 'guardian_relationship', 'guardian_phone',
            ]);

        $this->assertSame(0, Student::query()->count());
    }

    public function test_student_profile_page_shows_own_profile_only(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);
        $school = $this->school();

        $this->actingAs($user)->post('/profile', $this->validProfileData() + [
            'school_id' => $school->id,
        ]);

        $student = Student::query()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee($student->scholar_student_id)
            ->assertSee('Test Student');
    }

    public function test_student_cannot_access_admin_school_management(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)->get('/admin/schools')->assertForbidden();
        $this->actingAs($user)->post('/admin/schools', ['name' => 'Hack School'])->assertForbidden();
    }

    public function test_admin_can_create_school(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/schools', [
            'name' => 'New School',
            'school_code' => 'SCH-NEW-1',
            'province' => 'Bagmati Province',
            'district' => 'Dolakha',
            'municipality' => 'Bhimeshwar Municipality',
            'address' => 'Ward 1',
            'contact_phone' => '9800000000',
            'email' => 'info@new.edu.np',
            'status' => 'ACTIVE',
        ])->assertRedirect(route('admin.schools.index'));

        $this->assertDatabaseHas('schools', ['name' => 'New School', 'district' => 'Dolakha']);
    }

    public function test_guardian_user_cannot_access_student_profile_routes(): void
    {
        $guardianUser = User::factory()->guardian()->create();

        $this->actingAs($guardianUser)->get('/profile')->assertForbidden();
        $this->actingAs($guardianUser)->get('/guardian/profile')->assertOk();
    }

    public function test_guardian_can_link_a_student_by_scholar_student_id(): void
    {
        $guardianUser = User::factory()->guardian()->create();
        $guardian = Guardian::query()->create([
            'user_id' => $guardianUser->id,
            'name' => 'Link Guardian',
            'relationship' => 'Father',
            'phone' => '9800000001',
        ]);

        $service = app(StudentService::class);
        $student = Student::query()->create([
            'scholar_student_id' => $service->generateScholarStudentId(),
            'name' => 'Unlinked Student',
            'date_of_birth' => '2012-01-01',
            'gender' => 'MALE',
            'education_level' => 'SECONDARY',
            'grade' => 8,
            'verification_status' => Student::STATUS_UNVERIFIED,
        ]);

        $this->actingAs($guardianUser)
            ->post('/guardian/students', ['scholar_student_id' => $student->scholar_student_id])
            ->assertRedirect();

        $this->assertSame($guardian->id, $student->fresh()->guardian_id);
    }

    public function test_guardian_cannot_link_a_student_already_linked_to_another_guardian(): void
    {
        $guardianUser = User::factory()->guardian()->create();
        Guardian::query()->create([
            'user_id' => $guardianUser->id,
            'name' => 'Link Guardian',
            'relationship' => 'Father',
            'phone' => '9800000001',
        ]);

        $otherGuardian = Guardian::query()->create([
            'name' => 'Other Guardian',
            'relationship' => 'Mother',
            'phone' => '9800000002',
        ]);

        $service = app(StudentService::class);
        $student = Student::query()->create([
            'scholar_student_id' => $service->generateScholarStudentId(),
            'name' => 'Taken Student',
            'date_of_birth' => '2012-01-01',
            'gender' => 'MALE',
            'education_level' => 'SECONDARY',
            'grade' => 8,
            'guardian_id' => $otherGuardian->id,
            'verification_status' => Student::STATUS_UNVERIFIED,
        ]);

        $response = $this->actingAs($guardianUser)
            ->post('/guardian/students', ['scholar_student_id' => $student->scholar_student_id]);

        $response->assertSessionHasErrors('scholar_student_id');
        $this->assertSame($otherGuardian->id, $student->fresh()->guardian_id);
    }

    public function test_student_can_update_own_profile(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);
        $school = $this->school();

        $this->actingAs($user)->post('/profile', $this->validProfileData() + [
            'school_id' => $school->id,
        ]);

        $this->actingAs($user)
            ->put('/profile', array_merge($this->validProfileData(), [
                'school_id' => $school->id,
                'name' => 'Updated Name',
                'grade' => 10,
            ]))
            ->assertRedirect(route('profile.show'));

        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Updated Name', $student->name);
        $this->assertSame(10, $student->grade);
        $this->assertSame('Test Guardian', $student->guardian->name);
    }
}
