<?php

namespace Tests\Feature;

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
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectionTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-SEL'],
            [
                'name' => 'Selection School',
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
            'scholar_student_id' => 'SS-SEL-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Selection Student',
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

    private function scholarship(array $committeeIds = []): Scholarship
    {
        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Selection Scholarship',
            'description' => 'Used to test selection scoring.',
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

        $scholarship->criteria()->createMany([
            ['name' => 'Economic condition', 'description' => 'Household income', 'weight' => 60, 'maximum_score' => 100, 'order' => 0],
            ['name' => 'Academic performance', 'description' => 'Recent results', 'weight' => 40, 'maximum_score' => 100, 'order' => 1],
        ]);

        foreach ($committeeIds as $committeeId) {
            $scholarship->committeeMembers()->create(['user_id' => $committeeId]);
        }

        return $scholarship;
    }

    private function underReviewApplication(Scholarship $scholarship, Student $student): Application
    {
        return Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $student->user_id,
            'status' => ApplicationStatus::UNDER_REVIEW,
            'statement' => str_repeat('I need this scholarship to continue my studies. ', 5),
            'submitted_at' => now()->subDays(2),
        ]);
    }

    public function test_non_committee_member_cannot_access_selection_routes(): void
    {
        $studentUser = User::factory()->create(['role' => Role::STUDENT]);
        [, $student] = $this->student();
        $scholarship = $this->scholarship();
        $application = $this->underReviewApplication($scholarship, $student);

        $this->actingAs($studentUser)->get('/selection')->assertForbidden();
        $this->actingAs($studentUser)->get("/selection/{$application->id}")->assertForbidden();
    }

    public function test_committee_sees_only_assigned_scholarship_applications(): void
    {
        $mine = User::factory()->committee()->create();
        $other = User::factory()->committee()->create();

        $scholarship = $this->scholarship([$mine->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $otherScholarship = $this->scholarship();
        $otherApplication = $this->underReviewApplication($otherScholarship, $student->fresh());

        $this->actingAs($mine)->get('/selection')
            ->assertOk()
            ->assertSee('Selection Student')
            ->assertDontSee('other scholarship view');

        $this->actingAs($mine)->get("/selection/{$otherApplication->id}")->assertForbidden();

        $this->actingAs($other)->get('/selection')
            ->assertOk()
            ->assertDontSee('Selection Student');
    }

    public function test_committee_can_record_scores_and_weighted_total_is_calculated(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        [$economic, $academic] = $scholarship->criteria->all();

        $this->actingAs($committee)->patch("/selection/{$application->id}/scores", [
            'scores' => [
                $economic->id => 50,
                $academic->id => 80,
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('selection.show', $application));

        $application->refresh()->load('scores.criterion');
        $this->assertSame(2, $application->scores->count());
        $this->assertEqualsWithDelta(62.0, $application->weightedTotal(), 0.01);

        $this->actingAs($committee)->get("/selection/{$application->id}")
            ->assertOk()
            ->assertSee('62.00');
    }

    public function test_score_outside_criterion_maximum_is_rejected(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $economic = $scholarship->criteria->first();

        $this->actingAs($committee)->patch("/selection/{$application->id}/scores", [
            'scores' => [$economic->id => 150],
        ])->assertSessionHasErrors('scores.'.$economic->id);

        $this->assertSame(0, $application->scores()->count());
    }

    public function test_committee_can_record_a_selected_decision(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'SELECTED',
            'reason' => 'Highest weighted score with verified need.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('selection.show', $application));

        $application->refresh()->load('decision');
        $this->assertSame(ApplicationStatus::SELECTED, $application->status);
        $this->assertSame(Decision::SELECTED, $application->decision->decision);
        $this->assertSame($committee->id, $application->decision->decided_by_user_id);
        $this->assertNotNull($application->decision->decided_at);
    }

    public function test_decision_requires_a_meaningful_reason(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'REJECTED',
            'reason' => 'no',
        ])->assertSessionHasErrors('reason');

        $this->assertSame(ApplicationStatus::UNDER_REVIEW, $application->fresh()->status);
    }

    public function test_a_second_decision_is_rejected(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'WAITLISTED',
            'reason' => 'Strong candidate but no slot available now.',
        ]);

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'SELECTED',
            'reason' => 'Trying to change the decision afterwards.',
        ])->assertSessionHasErrors('decision');

        $this->assertSame(ApplicationStatus::WAITLISTED, $application->fresh()->status);
        $this->assertSame(Decision::WAITLISTED, $application->fresh()->decision->decision);
    }

    public function test_scoring_is_closed_after_a_decision(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);
        $economic = $scholarship->criteria->first();

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'SELECTED',
            'reason' => 'Clear winner on weighted score.',
        ]);

        $this->actingAs($committee)->patch("/selection/{$application->id}/scores", [
            'scores' => [$economic->id => 100],
        ])->assertForbidden();

        $this->assertSame(0, $application->scores()->count());
    }

    public function test_application_not_yet_verified_cannot_be_reviewed(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [, $student] = $this->student();
        $application = Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $student->user_id,
            'status' => ApplicationStatus::LOCAL_VERIFICATION,
            'statement' => str_repeat('Not verified yet, so no selection. ', 8),
            'submitted_at' => now(),
        ]);

        $this->actingAs($committee)->get('/selection')->assertOk()->assertDontSee('Selection Student');
        $this->actingAs($committee)->get("/selection/{$application->id}")->assertForbidden();
    }

    public function test_student_can_see_the_decision_reason_afterwards(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship([$committee->id]);
        [$studentUser, $student] = $this->student();
        $application = $this->underReviewApplication($scholarship, $student);

        $this->actingAs($committee)->patch("/selection/{$application->id}/decision", [
            'decision' => 'REJECTED',
            'reason' => 'Your GPA was below the published threshold for this round.',
        ]);

        $this->actingAs($studentUser)->get("/applications/{$application->id}")
            ->assertOk()
            ->assertSee('Rejected')
            ->assertSee('below the published threshold');
    }
}
