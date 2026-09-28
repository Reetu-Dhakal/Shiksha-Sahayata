<?php

namespace Tests\Feature;

use App\Enums\AppealStatus;
use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\SelectionDecision;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppealTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-APP2'],
            [
                'name' => 'Appeal School',
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
    private function student(): array
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $student = Student::query()->create([
            'scholar_student_id' => 'SS-APL-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Appeal Student',
            'date_of_birth' => '2011-05-02',
            'gender' => Gender::FEMALE,
            'education_level' => EducationLevel::SECONDARY,
            'grade' => 9,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'student_category' => StudentCategory::LOW_INCOME,
            'school_id' => $this->school()->id,
            'verification_status' => Student::STATUS_VERIFIED,
        ]);

        return [$user, $student];
    }

    private function rejectedApplication(array $committeeIds = []): array
    {
        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Appeal Scholarship',
            'description' => 'Used to test appeals.',
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

        foreach ($committeeIds as $committeeId) {
            $scholarship->committeeMembers()->create(['user_id' => $committeeId]);
        }

        [, $student] = $this->student();

        $application = Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $student->user_id,
            'status' => ApplicationStatus::REJECTED,
            'statement' => str_repeat('I need this scholarship to continue my studies. ', 5),
            'submitted_at' => now()->subDays(6),
        ]);

        SelectionDecision::query()->create([
            'application_id' => $application->id,
            'decision' => Decision::REJECTED,
            'reason' => 'Your score was below the cut-off for this round.',
            'decided_at' => now()->subDay(),
        ]);

        return [$scholarship, $application];
    }

    public function test_applicant_can_appeal_a_rejection(): void
    {
        [, $application] = $this->rejectedApplication();
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'I believe my economic condition score was not assessed correctly.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('applications.show', $application));

        $application->refresh();
        $this->assertSame(ApplicationStatus::APPEALED, $application->status);

        $appeal = $application->appeal()->firstOrFail();
        $this->assertSame(AppealStatus::SUBMITTED, $appeal->status);
        $this->assertSame($studentUser->id, $appeal->appellant_user_id);

        $this->actingAs($studentUser)->get("/applications/{$application->id}")
            ->assertOk()
            ->assertSee('Appeal')
            ->assertSee('not assessed correctly');
    }

    public function test_appeal_requires_a_meaningful_reason(): void
    {
        [, $application] = $this->rejectedApplication();
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'too short',
        ])->assertSessionHasErrors('reason');

        $this->assertSame(ApplicationStatus::REJECTED, $application->fresh()->status);
    }

    public function test_only_rejected_applications_can_be_appealed(): void
    {
        [, $application] = $this->rejectedApplication();
        $studentUser = $application->student->user;
        $application->update(['status' => ApplicationStatus::SELECTED]);

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'Attempting to appeal a selection that was actually favourable to me.',
        ])->assertSessionHasErrors('application');

        $this->assertSame(ApplicationStatus::SELECTED, $application->fresh()->status);
    }

    public function test_second_appeal_is_rejected(): void
    {
        [, $application] = $this->rejectedApplication();
        $studentUser = $application->student->user;

        $payload = ['reason' => 'The first appeal reason with enough characters to pass validation.'];

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", $payload);
        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", $payload)
            ->assertSessionHasErrors('application');

        $this->assertSame(1, $application->appeal()->count());
    }

    public function test_committee_reopens_appeal_and_approves_it(): void
    {
        $committee = User::factory()->committee()->create();
        [, $application] = $this->rejectedApplication([$committee->id]);
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'The economic score looks wrong given the documents I uploaded earlier.',
        ]);

        $appeal = $application->appeal()->firstOrFail();

        $this->actingAs($committee)->get('/appeals')->assertOk()->assertSee('Appeal Student');

        $this->actingAs($committee)->patch("/appeals/{$appeal->id}/decision", [
            'outcome' => 'APPROVED',
            'remarks' => 'Reopening because scoring notes were incomplete.',
        ])->assertSessionHasErrors('status');

        $this->actingAs($committee)->patch("/appeals/{$appeal->id}/reopen")
            ->assertSessionHasNoErrors();

        $this->assertSame(AppealStatus::UNDER_REVIEW, $appeal->fresh()->status);
        $this->assertSame(ApplicationStatus::UNDER_REVIEW, $application->fresh()->status);

        $this->actingAs($committee)->patch("/appeals/{$appeal->id}/decision", [
            'outcome' => 'APPROVED',
            'remarks' => 'Reopened scoring review accepted; decision will follow in selection.',
        ])->assertSessionHasNoErrors();

        $appeal->refresh();
        $this->assertSame(AppealStatus::APPROVED, $appeal->status);
        $this->assertSame($committee->id, $appeal->reviewed_by_user_id);
        $this->assertSame(ApplicationStatus::UNDER_REVIEW, $application->fresh()->status);
    }

    public function test_rejected_appeal_returns_application_to_rejected(): void
    {
        $committee = User::factory()->committee()->create();
        [, $application] = $this->rejectedApplication([$committee->id]);
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'I want the committee to look at my category certificate once more.',
        ]);

        $appeal = $application->appeal()->firstOrFail();
        $this->actingAs($committee)->patch("/appeals/{$appeal->id}/reopen");

        $this->actingAs($committee)->patch("/appeals/{$appeal->id}/decision", [
            'outcome' => 'REJECTED',
            'remarks' => 'The published criteria were applied correctly.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(AppealStatus::REJECTED, $appeal->fresh()->status);
        $this->assertSame(ApplicationStatus::REJECTED, $application->fresh()->status);

        $this->actingAs($studentUser)->get("/applications/{$application->id}")
            ->assertOk()
            ->assertSee('applied correctly');
    }

    public function test_committee_member_without_assignment_cannot_review_appeal(): void
    {
        $assigned = User::factory()->committee()->create();
        $outsider = User::factory()->committee()->create();
        [, $application] = $this->rejectedApplication([$assigned->id]);
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'Please review the scoring for economic condition once again.',
        ]);

        $appeal = $application->appeal()->firstOrFail();

        $this->actingAs($outsider)->get('/appeals')->assertOk()->assertDontSee('Appeal Student');
        $this->actingAs($outsider)->get("/appeals/{$appeal->id}")->assertForbidden();
        $this->actingAs($outsider)->patch("/appeals/{$appeal->id}/reopen")->assertForbidden();
    }

    public function test_student_cannot_access_appeal_review_routes(): void
    {
        $committee = User::factory()->committee()->create();
        [, $application] = $this->rejectedApplication([$committee->id]);
        $studentUser = $application->student->user;

        $this->actingAs($studentUser)->patch("/applications/{$application->id}/appeal", [
            'reason' => 'Requesting a formal review of the rejection decision.',
        ]);

        $appeal = $application->appeal()->firstOrFail();

        $this->actingAs($studentUser)->get('/appeals')->assertForbidden();
        $this->actingAs($studentUser)->get("/appeals/{$appeal->id}")->assertForbidden();
    }
}
