<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\AwardStatus;
use App\Enums\Decision;
use App\Enums\DisbursementStatus;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\Award;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-AWD'],
            [
                'name' => 'Award School',
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
            'scholar_student_id' => 'SS-AWD-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Award Student',
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

    private function scholarship(): Scholarship
    {
        $admin = User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Award Scholarship',
            'description' => 'Used to test award issuance and verification.',
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
            ['name' => 'Economic condition', 'description' => 'Household income', 'weight' => 100, 'maximum_score' => 100, 'order' => 0],
        ]);

        return $scholarship;
    }

    private function application(Scholarship $scholarship, Student $student, ApplicationStatus $status): Application
    {
        return Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $student->user_id,
            'status' => $status,
            'statement' => str_repeat('I need this scholarship to continue my studies. ', 5),
            'submitted_at' => now()->subDays(2),
        ]);
    }

    private function selectedApplication(): array
    {
        $scholarship = $this->scholarship();
        [$studentUser, $student] = $this->student();
        $application = $this->application($scholarship, $student, ApplicationStatus::SELECTED);

        $application->decision()->create([
            'decision' => Decision::SELECTED,
            'reason' => 'Highest weighted score with verified need.',
            'decided_by_user_id' => $scholarship->created_by,
            'decided_at' => now(),
        ]);

        return [$application, $studentUser];
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_only_admin_can_issue_an_award(): void
    {
        $committee = User::factory()->committee()->create();
        $studentUser = User::factory()->create(['role' => Role::STUDENT]);
        $scholarship = $this->scholarship();
        [, $student] = $this->student();
        $application = $this->application($scholarship, $student, ApplicationStatus::SELECTED);

        $this->actingAs($committee)->post(route('admin.awards.issue', $application))->assertForbidden();
        $this->actingAs($studentUser)->get(route('admin.awards.index'))->assertForbidden();

        $this->assertSame(ApplicationStatus::SELECTED, $application->fresh()->status);
        $this->assertSame(0, Award::query()->count());
    }

    public function test_admin_issues_an_award_for_a_selected_application(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();

        $this->actingAs($admin)->post(route('admin.awards.issue', $application))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $application->refresh();
        $this->assertSame(ApplicationStatus::AWARDED, $application->status);

        $award = $application->award;
        $this->assertNotNull($award);
        $this->assertMatchesRegularExpression('/^SS-AWD-\d{4}-\d{4}$/', $award->award_number);
        $this->assertSame(strlen($award->verification_code), 24);
        $this->assertSame(AwardStatus::ACTIVE, $award->status);
        $this->assertSame(DisbursementStatus::NOT_STARTED, $award->disbursement_status);
        $this->assertSame($admin->id, $award->issued_by_user_id);
    }

    public function test_award_numbers_are_sequential_and_unique(): void
    {
        $admin = $this->admin();
        [$first] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $first));

        [$second] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $second));

        $numbers = Award::query()->orderBy('id')->pluck('award_number');
        $this->assertSame(2, $numbers->count());
        $this->assertNotSame($numbers[0], $numbers[1]);
    }

    public function test_cannot_issue_an_award_twice_or_for_an_unselected_application(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();

        $this->actingAs($admin)->post(route('admin.awards.issue', $application));
        $this->actingAs($admin)->post(route('admin.awards.issue', $application))
            ->assertSessionHasErrors('application');

        [, $student] = $this->student();
        $draft = $this->application($application->scholarship, $student, ApplicationStatus::UNDER_REVIEW);
        $this->actingAs($admin)->post(route('admin.awards.issue', $draft))
            ->assertSessionHasErrors('application');

        $this->assertSame(1, Award::query()->count());
    }

    public function test_public_verification_page_shows_limited_information(): void
    {
        [$application] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $application));

        $award = $application->fresh()->award;

        $this->get(route('verify.award.show', $award->verification_code))
            ->assertOk()
            ->assertSee($award->award_number)
            ->assertSee($application->scholarship->title)
            ->assertSee('Award Student')
            ->assertSee('Active')
            ->assertDontSee($application->statement)
            ->assertDontSee($application->student->user->email);
    }

    public function test_unknown_verification_code_is_reported_as_not_found(): void
    {
        $this->get(route('verify.award.show', 'DOESNOTEXISTCODE1234567890'))
            ->assertOk()
            ->assertSee('No award found');
    }

    public function test_verification_form_redirects_to_the_result_page(): void
    {
        [$application] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        $this->get(route('verify.award.form', ['code' => $award->verification_code]))
            ->assertRedirect(route('verify.award.show', $award->verification_code));
    }

    public function test_applicant_sees_only_their_own_awards(): void
    {
        [$application, $applicant] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $application));

        [$otherApplication] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $otherApplication));

        $this->actingAs($applicant)->get(route('awards.index'))
            ->assertOk()
            ->assertSee($application->fresh()->award->award_number)
            ->assertDontSee($otherApplication->fresh()->award->award_number);
    }

    public function test_applicant_can_download_the_award_letter_as_a_pdf(): void
    {
        [$application, $applicant] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        $response = $this->actingAs($applicant)->get(route('awards.letter', $award));

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
    }

    public function test_other_students_cannot_download_the_award_letter(): void
    {
        [$application] = $this->selectedApplication();
        $this->actingAs($this->admin())->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        [$intruder] = $this->student();

        $this->actingAs($intruder)->get(route('awards.letter', $award))->assertForbidden();
    }

    public function test_disbursement_advances_one_step_at_a_time(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        $this->actingAs($admin)->patch(route('admin.awards.disbursement', $award), [
            'disbursement_status' => 'RELEASED',
        ])->assertSessionHasErrors('disbursement');

        $this->actingAs($admin)->patch(route('admin.awards.disbursement', $award), [
            'disbursement_status' => 'PROCESSING',
            'remarks' => 'Transfer initiated from the treasury account.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(DisbursementStatus::PROCESSING, $award->fresh()->disbursement_status);
        $this->assertSame(ApplicationStatus::AWARDED, $application->fresh()->status);
    }

    public function test_confirmed_disbursement_updates_the_application(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        foreach (['PROCESSING', 'RELEASED', 'RECEIVED', 'CONFIRMED'] as $status) {
            $this->actingAs($admin)->patch(route('admin.awards.disbursement', $award), [
                'disbursement_status' => $status,
                'remarks' => 'Recorded by the administering office.',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(DisbursementStatus::CONFIRMED, $award->fresh()->disbursement_status);
        $this->assertSame(ApplicationStatus::DISBURSEMENT_CONFIRMED, $application->fresh()->status);
    }

    public function test_revoked_award_is_flagged_on_the_verification_page(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        $this->actingAs($admin)->patch(route('admin.awards.revoke', $award), [
            'reason' => 'Award issued against the published selection list by mistake.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(AwardStatus::REVOKED, $award->fresh()->status);

        $this->get(route('verify.award.show', $award->verification_code))
            ->assertOk()
            ->assertSee('Revoked')
            ->assertSee('must not be treated as valid');
    }

    public function test_revocation_requires_a_reason(): void
    {
        $admin = $this->admin();
        [$application] = $this->selectedApplication();
        $this->actingAs($admin)->post(route('admin.awards.issue', $application));
        $award = $application->fresh()->award;

        $this->actingAs($admin)->patch(route('admin.awards.revoke', $award), [
            'reason' => 'no',
        ])->assertSessionHasErrors('reason');

        $this->assertSame(AwardStatus::ACTIVE, $award->fresh()->status);
    }
}
