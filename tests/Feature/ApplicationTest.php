<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\Guardian;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-APP'],
            [
                'name' => 'Application Test School',
                'province' => 'Bagmati Province',
                'district' => 'Kavrepalanchok',
                'municipality' => 'Dhulikhel Municipality',
                'status' => School::STATUS_ACTIVE,
            ]
        );
    }

    /**
     * @return array{0: User, 1: Student}
     */
    private function student(array $userOverrides = []): array
    {
        $user = User::factory()->create(array_merge(['role' => Role::STUDENT], $userOverrides));

        $student = Student::query()->create([
            'scholar_student_id' => 'SS-TEST-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Aasha Student',
            'date_of_birth' => '2011-05-02',
            'gender' => Gender::FEMALE,
            'education_level' => EducationLevel::SECONDARY,
            'grade' => 9,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'student_category' => StudentCategory::LOW_INCOME,
            'school_id' => $this->school()->id,
            'verification_status' => Student::STATUS_UNVERIFIED,
        ]);

        return [$user, $student];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function scholarship(array $overrides = []): Scholarship
    {
        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->admin()->create();

        return Scholarship::query()->create(array_merge([
            'title' => 'Open Scholarship',
            'description' => 'Support for students.',
            'provider' => 'Shiksha Sahayat Fund',
            'application_start' => now()->subDays(5),
            'application_deadline' => now()->addDays(20),
            'education_level' => EducationLevel::SECONDARY,
            'target_grade_min' => 9,
            'target_grade_max' => 10,
            'available_slots' => 5,
            'status' => ScholarshipStatus::PUBLISHED,
            'created_by' => $admin->id,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function validStatement(): array
    {
        return ['statement' => str_repeat('I need this scholarship to continue my studies. ', 5)];
    }

    public function test_student_without_profile_is_redirected_to_create_profile(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($user)->get('/applications/create')->assertRedirect(route('profile.create'));
    }

    public function test_student_can_create_edit_and_submit_a_draft_application(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship();

        $response = $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement());

        $application = Application::query()->firstOrFail();
        $response->assertRedirect(route('applications.show', $application));

        $this->assertSame(ApplicationStatus::DRAFT, $application->status);
        $this->assertFalse($application->is_assisted);
        $this->assertSame($user->id, $application->submitted_by_user_id);

        $this->actingAs($user)->put("/applications/{$application->id}", [
            'statement' => str_repeat('Updated statement about my need for support and goals. ', 4),
            'current_grade' => 9,
            'grade_point_average' => 3.5,
        ])->assertRedirect(route('applications.show', $application));

        $this->actingAs($user)->patch("/applications/{$application->id}/submit")
            ->assertRedirect(route('applications.show', $application));

        $application->refresh();
        $this->assertSame(ApplicationStatus::SUBMITTED, $application->status);
        $this->assertNotNull($application->submitted_at);
        $this->assertSame(9, $application->current_grade);
        $this->assertEquals(3.5, (float) $application->grade_point_average);
    }

    public function test_statement_requires_minimum_length(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'statement' => 'Too short.',
        ])->assertSessionHasErrors('statement');

        $this->assertSame(0, Application::query()->count());
    }

    public function test_duplicate_application_for_same_scholarship_is_rejected(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement());

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement())->assertSessionHasErrors('scholarship');

        $this->assertSame(1, Application::query()->count());
    }

    public function test_application_cannot_be_created_after_deadline(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship([
            'application_start' => now()->subDays(30),
            'application_deadline' => now()->subDay(),
        ]);

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement())->assertSessionHasErrors('scholarship');

        $this->assertSame(0, Application::query()->count());
    }

    public function test_submitted_application_cannot_be_edited_or_withdrawn(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement());

        $application = Application::query()->firstOrFail();
        $this->actingAs($user)->patch("/applications/{$application->id}/submit");

        $this->actingAs($user)->get("/applications/{$application->id}/edit")
            ->assertRedirect(route('applications.show', $application));

        $this->actingAs($user)->delete("/applications/{$application->id}")
            ->assertSessionHasErrors('application');

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
    }

    public function test_applications_cannot_be_submitted_twice(): void
    {
        [$user, $student] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($user)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement());

        $application = Application::query()->firstOrFail();
        $this->actingAs($user)->patch("/applications/{$application->id}/submit");
        $this->actingAs($user)->patch("/applications/{$application->id}/submit")
            ->assertSessionHasErrors('application');

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
    }

    public function test_student_cannot_view_another_students_application(): void
    {
        [$owner] = $this->student();
        [$intruder] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($owner)->post('/applications', [
            'scholarship_id' => $scholarship->id,
        ] + $this->validStatement());

        $application = Application::query()->firstOrFail();

        $this->actingAs($intruder)->get("/applications/{$application->id}")->assertForbidden();
        $this->actingAs($intruder)->patch("/applications/{$application->id}/submit")->assertForbidden();
    }

    public function test_applications_index_shows_only_own_applications(): void
    {
        [$owner] = $this->student();
        [$other] = $this->student();
        $scholarship = $this->scholarship();

        $this->actingAs($owner)->post('/applications', [
            'scholarship_id' => $scholarship->id,
        ] + $this->validStatement());

        $this->actingAs($other)->get('/applications')
            ->assertOk()
            ->assertDontSee('Open Scholarship')
            ->assertSee('No applications yet');

        $this->actingAs($owner)->get('/applications')
            ->assertOk()
            ->assertSee('Open Scholarship');
    }

    public function test_guardian_can_apply_for_linked_student(): void
    {
        $guardianUser = User::factory()->guardian()->create();
        $guardian = Guardian::query()->create([
            'user_id' => $guardianUser->id,
            'name' => 'Radha Guardian',
            'relationship' => 'Mother',
            'phone' => '9800000011',
        ]);

        $studentUser = User::factory()->create(['role' => Role::STUDENT]);
        $student = Student::query()->create([
            'scholar_student_id' => 'SS-GUARD-'.uniqid(),
            'user_id' => $studentUser->id,
            'guardian_id' => $guardian->id,
            'name' => 'Linked Student',
            'date_of_birth' => '2011-01-01',
            'gender' => Gender::MALE,
            'education_level' => EducationLevel::SECONDARY,
            'grade' => 9,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'student_category' => StudentCategory::LOW_INCOME,
            'school_id' => $this->school()->id,
            'verification_status' => Student::STATUS_UNVERIFIED,
        ]);

        $scholarship = $this->scholarship();

        $this->actingAs($guardianUser)->get('/applications/create')->assertOk();

        $this->actingAs($guardianUser)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement());

        $application = Application::query()->firstOrFail();
        $this->assertSame($student->id, $application->student_id);
        $this->assertFalse($application->is_assisted);

        $this->actingAs($guardianUser)->get('/applications')
            ->assertOk()
            ->assertSee('Open Scholarship');
    }

    public function test_school_officer_cannot_access_applicant_routes(): void
    {
        [, $student] = $this->student();
        $scholarship = $this->scholarship();
        $officer = User::factory()->schoolOfficer()->create(['school_id' => $student->school_id]);

        $this->actingAs($officer)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
        ] + $this->validStatement())->assertForbidden();
    }

    public function test_admin_dashboard_role_cannot_access_applicant_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/applications')->assertForbidden();
    }
}
