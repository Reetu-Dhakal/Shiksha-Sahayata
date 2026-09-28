<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Enums\VerificationStage;
use App\Enums\VerificationStatus;
use App\Models\Application;
use App\Models\LocalEducationUnit;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-VER'],
            [
                'name' => 'Verification School',
                'province' => 'Bagmati Province',
                'district' => 'Kavrepalanchok',
                'municipality' => 'Dhulikhel Municipality',
                'status' => School::STATUS_ACTIVE,
            ]
        );
    }

    private function localUnit(): LocalEducationUnit
    {
        return LocalEducationUnit::query()->firstOrCreate(
            ['name' => 'Kavre LEU'],
            [
                'province' => 'Bagmati Province',
                'district' => 'Kavrepalanchok',
                'municipality' => 'Dhulikhel Municipality',
                'status' => 'ACTIVE',
            ]
        );
    }

    /**
     * @return array{0: User, 1: Student}
     */
    private function student(): array
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $student = Student::query()->create([
            'scholar_student_id' => 'SS-VER-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Verify Student',
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

    private function scholarship(): Scholarship
    {
        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->admin()->create();

        return Scholarship::query()->create([
            'title' => 'Verification Scholarship',
            'description' => 'Used to test the verification workflow.',
            'provider' => 'Shiksha Sahayat Fund',
            'application_start' => now()->subDays(5),
            'application_deadline' => now()->addDays(20),
            'education_level' => EducationLevel::SECONDARY,
            'target_grade_min' => 9,
            'target_grade_max' => 10,
            'available_slots' => 5,
            'status' => ScholarshipStatus::PUBLISHED,
            'created_by' => $admin->id,
        ]);
    }

    private function submittedApplication(Student $student): Application
    {
        return Application::query()->create([
            'scholarship_id' => $this->scholarship()->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $student->user_id,
            'status' => ApplicationStatus::SUBMITTED,
            'statement' => str_repeat('I need this scholarship to continue my studies. ', 5),
            'submitted_at' => now(),
        ]);
    }

    private function schoolOfficer(): User
    {
        return User::factory()->schoolOfficer()->create(['school_id' => $this->school()->id]);
    }

    private function localOfficer(): User
    {
        return User::factory()->localOfficer()->create(['local_education_unit_id' => $this->localUnit()->id]);
    }

    public function test_school_officer_queue_shows_only_their_own_school(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $otherSchool = School::query()->create([
            'name' => 'Other School',
            'school_code' => 'SCH-OTHER',
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Banepa Municipality',
            'status' => School::STATUS_ACTIVE,
        ]);
        $otherOfficer = User::factory()->schoolOfficer()->create(['school_id' => $otherSchool->id]);

        $this->actingAs($this->schoolOfficer())->get('/verifications')
            ->assertOk()
            ->assertSee('Verify Student');

        $this->actingAs($otherOfficer)->get('/verifications')
            ->assertOk()
            ->assertDontSee('Verify Student')
            ->assertSee('Nothing waiting for verification');

        $this->actingAs($otherOfficer)->get("/verifications/{$application->id}")->assertForbidden();
    }

    public function test_school_officer_can_start_review_and_approve(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);
        $officer = $this->schoolOfficer();

        $this->actingAs($officer)->patch("/verifications/{$application->id}/start")
            ->assertRedirect(route('verifications.show', $application));

        $this->assertSame(ApplicationStatus::SCHOOL_VERIFICATION, $application->fresh()->status);

        $this->actingAs($officer)->patch("/verifications/{$application->id}/approve", [
            'remarks' => 'Enrollment and documents verified at school.',
        ])->assertSessionHasNoErrors();

        $application->refresh()->load('verifications');
        $this->assertSame(ApplicationStatus::LOCAL_VERIFICATION, $application->status);

        $verification = $application->verificationFor(VerificationStage::SCHOOL);
        $this->assertNotNull($verification);
        $this->assertSame(VerificationStatus::VERIFIED, $verification->status);
        $this->assertSame($officer->id, $verification->officer_user_id);
        $this->assertNotNull($verification->decided_at);
    }

    public function test_school_officer_can_return_application_with_remarks(): void
    {
        [$studentUser, $student] = $this->student();
        $application = $this->submittedApplication($student);
        $officer = $this->schoolOfficer();

        $this->actingAs($officer)->patch("/verifications/{$application->id}/return", [
            'remarks' => 'Please upload a clearer birth certificate.',
        ])->assertSessionHasNoErrors();

        $application->refresh();
        $this->assertSame(ApplicationStatus::RETURNED_FOR_CORRECTION, $application->status);
        $this->assertSame('Please upload a clearer birth certificate.', $application->return_remarks);

        $verification = $application->verificationFor(VerificationStage::SCHOOL);
        $this->assertSame(VerificationStatus::RETURNED, $verification->status);

        $this->actingAs($studentUser)->get("/applications/{$application->id}")
            ->assertOk()
            ->assertSee('Returned for correction')
            ->assertSee('clearer birth certificate');
    }

    public function test_return_requires_remarks(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $this->actingAs($this->schoolOfficer())->patch("/verifications/{$application->id}/return", [
            'remarks' => 'short',
        ])->assertSessionHasErrors('remarks');

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
    }

    public function test_local_officer_cannot_decide_school_stage(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $this->actingAs($this->localOfficer())->patch("/verifications/{$application->id}/approve", [
            'remarks' => 'Trying to approve at the wrong stage.',
        ])->assertForbidden();

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
    }

    public function test_local_officer_forwards_application_to_selection(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);
        $schoolOfficer = $this->schoolOfficer();
        $localOfficer = $this->localOfficer();

        $this->actingAs($schoolOfficer)->patch("/verifications/{$application->id}/start");
        $this->actingAs($schoolOfficer)->patch("/verifications/{$application->id}/approve", [
            'remarks' => 'School verification complete.',
        ]);

        $this->actingAs($localOfficer)->get('/verifications')->assertOk()->assertSee('Verify Student');

        $this->actingAs($localOfficer)->patch("/verifications/{$application->id}/approve", [
            'remarks' => 'Local criteria confirmed.',
        ])->assertSessionHasNoErrors();

        $application->refresh()->load('verifications');
        $this->assertSame(ApplicationStatus::UNDER_REVIEW, $application->status);

        $local = $application->verificationFor(VerificationStage::LOCAL);
        $this->assertSame(VerificationStatus::VERIFIED, $local->status);
        $this->assertSame($localOfficer->id, $local->officer_user_id);
    }

    public function test_local_officer_outside_jurisdiction_cannot_review(): void
    {
        [, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $otherUnit = LocalEducationUnit::query()->create([
            'name' => 'Other LEU',
            'province' => 'Bagmati Province',
            'district' => 'Sindhupalchok',
            'municipality' => 'Chautara Municipality',
            'status' => 'ACTIVE',
        ]);
        $otherOfficer = User::factory()->localOfficer()->create(['local_education_unit_id' => $otherUnit->id]);

        $this->actingAs($otherOfficer)->get('/verifications')
            ->assertOk()
            ->assertDontSee('Verify Student');

        $this->actingAs($otherOfficer)->get("/verifications/{$application->id}")->assertForbidden();
    }

    public function test_student_cannot_access_verification_routes(): void
    {
        [$studentUser, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $this->actingAs($studentUser)->get('/verifications')->assertForbidden();
        $this->actingAs($studentUser)->get("/verifications/{$application->id}")->assertForbidden();
        $this->actingAs($studentUser)->patch("/verifications/{$application->id}/start")->assertForbidden();
    }

    public function test_returned_application_can_be_corrected_and_resubmitted(): void
    {
        [$studentUser, $student] = $this->student();
        $application = $this->submittedApplication($student);

        $this->actingAs($this->schoolOfficer())->patch("/verifications/{$application->id}/return", [
            'remarks' => 'Please confirm your grade with the school.',
        ]);

        $this->actingAs($studentUser)->put("/applications/{$application->id}", [
            'statement' => str_repeat('Corrected statement after verification remarks were received. ', 4),
        ])->assertRedirect(route('applications.show', $application));

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/submit")
            ->assertRedirect(route('applications.show', $application));

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);

        $this->actingAs($this->schoolOfficer())->get('/verifications')->assertSee('Verify Student');
    }
}
