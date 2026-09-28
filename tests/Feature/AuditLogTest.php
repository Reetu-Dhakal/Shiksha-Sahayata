<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\AuditLog;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-AUD'],
            [
                'name' => 'Audit School',
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
            'scholar_student_id' => 'SS-AUD-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Audit Student',
            'date_of_birth' => '2011-05-02',
            'gender' => Gender::MALE,
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

    private function scholarship(ScholarshipStatus $status = ScholarshipStatus::DRAFT): Scholarship
    {
        $admin = User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Audit Scholarship',
            'description' => 'Used to test audit logging.',
            'provider' => 'Shiksha Sahayat Fund',
            'application_start' => now()->subDays(5),
            'application_deadline' => now()->addDays(20),
            'education_level' => EducationLevel::SECONDARY,
            'target_grade_min' => 9,
            'target_grade_max' => 10,
            'available_slots' => 5,
            'status' => $status,
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

    public function test_application_submission_is_audited(): void
    {
        [$applicant, $student] = $this->student();
        $scholarship = $this->scholarship(ScholarshipStatus::PUBLISHED);

        $application = Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $applicant->id,
            'status' => ApplicationStatus::DRAFT,
            'statement' => str_repeat('I need this scholarship to continue my studies. ', 5),
        ]);

        ApplicationDocument::query()->create([
            'application_id' => $application->id,
            'required_document_id' => $scholarship->requiredDocuments()->first()?->id,
            'uploaded_by_user_id' => $applicant->id,
            'document_type' => DocumentType::ACADEMIC_REPORT->value,
            'original_filename' => 'academic-report.pdf',
            'path' => 'applications/'.$application->id.'/ACADEMIC_REPORT/report.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
        ]);

        $this->actingAs($applicant)->patch(route('applications.submit', $application))
            ->assertSessionHasNoErrors();

        $log = AuditLog::query()->where('action', 'application.submit')->first();

        $this->assertNotNull($log);
        $this->assertSame($applicant->id, $log->user_id);
        $this->assertSame(Application::class, $log->subject_type);
        $this->assertSame($application->id, $log->subject_id);
        $this->assertNotNull($log->ip_address);
        $this->assertSame(['status' => 'SUBMITTED'], $log->new_values);
    }

    public function test_scholarship_status_change_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = $this->scholarship(ScholarshipStatus::DRAFT);

        $this->actingAs($admin)->patch(route('admin.scholarships.status', $scholarship), [
            'status' => 'PUBLISHED',
        ])->assertSessionHasNoErrors();

        $log = AuditLog::query()->where('action', 'scholarship.status')->first();

        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame(['status' => 'DRAFT'], $log->old_values);
        $this->assertSame(['status' => 'PUBLISHED'], $log->new_values);
    }

    public function test_admin_can_view_and_filter_the_audit_log(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = $this->scholarship(ScholarshipStatus::DRAFT);

        $this->actingAs($admin)->patch(route('admin.scholarships.status', $scholarship), [
            'status' => 'PUBLISHED',
        ]);

        $this->actingAs($admin)->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('scholarship.status')
            ->assertSee('Audit Scholarship');

        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['action' => 'scholarship.status']))
            ->assertOk()
            ->assertSee('Audit Scholarship');

        $this->actingAs($admin)->get(route('admin.audit-logs.index', ['action' => 'award.issue']))
            ->assertOk()
            ->assertDontSee('Audit Scholarship');
    }

    public function test_only_admins_can_view_the_audit_log(): void
    {
        $committee = User::factory()->committee()->create();
        [$applicant] = $this->student();

        $this->actingAs($committee)->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($applicant)->get(route('admin.audit-logs.index'))->assertForbidden();
    }
}
