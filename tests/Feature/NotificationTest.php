<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Notification;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-NTF'],
            [
                'name' => 'Notification School',
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
            'scholar_student_id' => 'SS-NTF-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Notification Student',
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

    private function schoolOfficer(): User
    {
        return User::factory()->create([
            'role' => Role::SCHOOL_OFFICER,
            'school_id' => $this->school()->id,
        ]);
    }

    private function scholarship(): Scholarship
    {
        $admin = User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Notification Scholarship',
            'description' => 'Used to test workflow notifications.',
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
            'submitted_at' => now()->subDays(1),
        ]);
    }

    private function uploadRequiredDocument(Application $application, Scholarship $scholarship, User $uploader): void
    {
        $required = $scholarship->requiredDocuments()->first();

        ApplicationDocument::query()->create([
            'application_id' => $application->id,
            'required_document_id' => $required?->id,
            'uploaded_by_user_id' => $uploader->id,
            'document_type' => DocumentType::ACADEMIC_REPORT->value,
            'original_filename' => 'academic-report.pdf',
            'path' => 'applications/'.$application->id.'/ACADEMIC_REPORT/report.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
        ]);
    }

    public function test_school_officer_is_notified_when_an_application_is_submitted(): void
    {
        $officer = $this->schoolOfficer();
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship();
        $application = $this->application($scholarship, $student, ApplicationStatus::DRAFT);
        $this->uploadRequiredDocument($application, $scholarship, $applicant);

        $this->actingAs($applicant)->patch(route('applications.submit', $application))
            ->assertSessionHasNoErrors();

        $this->assertTrue(
            Notification::query()
                ->where('user_id', $officer->id)
                ->where('type', NotificationType::APPLICATION_SUBMITTED->value)
                ->exists()
        );
    }

    public function test_applicant_is_notified_when_the_application_is_returned_for_correction(): void
    {
        $officer = $this->schoolOfficer();
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship();
        $application = $this->application($scholarship, $student, ApplicationStatus::SUBMITTED);

        $remarks = 'Please upload a clearer scan of the academic report.';

        $this->actingAs($officer)->patch(route('verifications.return', $application), [
            'remarks' => $remarks,
        ])->assertSessionHasNoErrors();

        $this->assertTrue(
            Notification::query()
                ->where('user_id', $applicant->id)
                ->where('type', NotificationType::APPLICATION_RETURNED->value)
                ->exists()
        );

        $this->actingAs($applicant)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Corrections required')
            ->assertSee($remarks);
    }

    public function test_selection_decision_notifies_the_applicant(): void
    {
        $committee = User::factory()->committee()->create();
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship();
        $scholarship->committeeMembers()->create(['user_id' => $committee->id]);
        $application = $this->application($scholarship, $student, ApplicationStatus::UNDER_REVIEW);

        $this->actingAs($committee)->patch(route('selection.decision', $application), [
            'decision' => 'SELECTED',
            'reason' => 'Highest weighted score among all applicants.',
        ])->assertSessionHasNoErrors();

        $notification = Notification::query()
            ->where('user_id', $applicant->id)
            ->where('type', NotificationType::SELECTION_DECIDED->value)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame(Decision::SELECTED->value, $notification->params['decision'] ?? null);
        $this->assertStringContainsString('Selected', $notification->body());
    }

    public function test_award_issuance_notifies_the_applicant(): void
    {
        $admin = User::factory()->admin()->create();
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship();
        $application = $this->application($scholarship, $student, ApplicationStatus::SELECTED);

        $this->actingAs($admin)->post(route('admin.awards.issue', $application))
            ->assertSessionHasNoErrors();

        $notification = Notification::query()
            ->where('user_id', $applicant->id)
            ->where('type', NotificationType::AWARD_ISSUED->value)
            ->first();

        $this->assertNotNull($notification);
        $this->assertNotNull($notification->link);
    }

    public function test_notifications_can_be_marked_as_read(): void
    {
        [$applicant] = $this->student();
        $service = app(NotificationService::class);

        $service->notify($applicant, NotificationType::SELECTION_DECIDED, [
            'scholarship' => 'Notification Scholarship',
            'decision' => 'Waitlisted',
            'reason' => 'No slot available right now.',
        ], route('notifications.index'));

        $this->actingAs($applicant)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Unread');

        $this->actingAs($applicant)->post(route('notifications.read-all'))
            ->assertSessionHasNoErrors();

        $this->assertNotNull(
            Notification::query()->where('user_id', $applicant->id)->first()?->read_at
        );
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        [$applicant] = $this->student();
        $other = User::factory()->create(['role' => Role::STUDENT]);

        $notification = app(NotificationService::class)->notify($other, NotificationType::AWARD_ISSUED, [
            'scholarship' => 'Notification Scholarship',
            'award' => 'SS-AWD-2026-0001',
        ]);

        $this->actingAs($applicant)->post(route('notifications.read', $notification))->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }
}
