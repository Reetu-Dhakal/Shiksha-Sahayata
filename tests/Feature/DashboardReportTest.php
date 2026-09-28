<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-DSH'],
            [
                'name' => 'Dashboard School',
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
            'scholar_student_id' => 'SS-DSH-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Dashboard Student',
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
            'title' => 'Dashboard Scholarship',
            'description' => 'Used to test dashboards and reports.',
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

        $scholarship->criteria()->create([
            'name' => 'Economic condition',
            'description' => 'Household income',
            'weight' => 100,
            'maximum_score' => 100,
            'order' => 0,
        ]);

        $scholarship->requiredDocuments()->create([
            'document_type' => DocumentType::ACADEMIC_REPORT,
            'is_required' => true,
            'label' => 'Academic report',
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
            'submitted_at' => now()->subDay(),
        ]);
    }

    public function test_admin_dashboard_shows_live_workflow_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = $this->scholarship();
        [, $student] = $this->student();
        $this->application($scholarship, $student, ApplicationStatus::SUBMITTED);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Administration Dashboard')
            ->assertSee('Published scholarships')
            ->assertSee('Applications by status')
            ->assertSee('Recent activity')
            ->assertSee('Submitted');
    }

    public function test_student_dashboard_shows_their_applications_and_notifications(): void
    {
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship();
        $this->application($scholarship, $student, ApplicationStatus::UNDER_REVIEW);

        app(NotificationService::class)->notify($applicant, NotificationType::SELECTION_DECIDED, [
            'scholarship' => 'Dashboard Scholarship',
            'decision' => 'Under review',
            'reason' => 'Waiting for committee scoring.',
        ], route('applications.index'));

        $this->actingAs($applicant)->get('/dashboard')
            ->assertOk()
            ->assertSee('Student Dashboard')
            ->assertSee('Dashboard Scholarship')
            ->assertSee('Selection decision recorded')
            ->assertSee('Unread notifications');
    }

    public function test_school_officer_dashboard_shows_the_pending_verification_queue(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school()->id]);
        [, $student] = $this->student();
        $scholarship = $this->scholarship();
        $this->application($scholarship, $student, ApplicationStatus::SUBMITTED);

        $this->actingAs($officer)->get('/dashboard')
            ->assertOk()
            ->assertSee('School Dashboard')
            ->assertSee('Pending verifications')
            ->assertSee('Dashboard Student')
            ->assertSee('Assisted applications');
    }

    public function test_committee_dashboard_shows_applications_ready_for_review(): void
    {
        $committee = User::factory()->committee()->create();
        $scholarship = $this->scholarship();
        $scholarship->committeeMembers()->create(['user_id' => $committee->id]);
        [, $student] = $this->student();
        $this->application($scholarship, $student, ApplicationStatus::UNDER_REVIEW);

        $this->actingAs($committee)->get('/dashboard')
            ->assertOk()
            ->assertSee('Selection Committee Dashboard')
            ->assertSee('Ready for review')
            ->assertSee('Dashboard Student');
    }

    public function test_reports_page_is_admin_only(): void
    {
        $committee = User::factory()->committee()->create();
        [$applicant] = $this->student();

        $this->actingAs($committee)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($applicant)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($applicant)->get(route('admin.reports.applications-csv'))->assertForbidden();
    }

    public function test_reports_page_aggregates_application_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = $this->scholarship();
        [, $first] = $this->student();
        [, $second] = $this->student();

        $this->application($scholarship, $first, ApplicationStatus::SUBMITTED);
        $this->application($scholarship, $second, ApplicationStatus::UNDER_REVIEW);

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Applications by status')
            ->assertSee('Applications by scholarship')
            ->assertSee('Applications by district')
            ->assertSee('Verification outcomes')
            ->assertSee('Dashboard Scholarship')
            ->assertSee('Kavrepalanchok');
    }

    public function test_applications_csv_export_contains_the_workflow_columns(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = $this->scholarship();
        [, $student] = $this->student();
        $application = $this->application($scholarship, $student, ApplicationStatus::SUBMITTED);

        $response = $this->actingAs($admin)->get(route('admin.reports.applications-csv'));

        $response->assertOk();
        $this->assertStringContainsString('csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('application_id', $csv);
        $this->assertStringContainsString('Dashboard Scholarship', $csv);
        $this->assertStringContainsString('assisted', $csv);
        $this->assertStringContainsString((string) $application->id, $csv);
    }
}
