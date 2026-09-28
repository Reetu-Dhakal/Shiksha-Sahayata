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
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function school(): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => 'SCH-DOC'],
            [
                'name' => 'Document Test School',
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
            'scholar_student_id' => 'SS-DOC-'.uniqid(),
            'user_id' => $user->id,
            'name' => 'Document Student',
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

    private function scholarshipWithDocuments(): Scholarship
    {
        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Documented Scholarship',
            'description' => 'Requires supporting documents.',
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

        $scholarship->requiredDocuments()->createMany([
            ['document_type' => DocumentType::BIRTH_REGISTRATION, 'description' => 'Birth certificate', 'is_required' => true, 'order' => 0],
            ['document_type' => DocumentType::ACADEMIC_REPORT, 'description' => 'Last report', 'is_required' => false, 'order' => 1],
        ]);

        return $scholarship;
    }

    private function draftApplication(User $user, Scholarship $scholarship, ?Student $student = null): Application
    {
        $student ??= $user->student;

        return Application::query()->create([
            'scholarship_id' => $scholarship->id,
            'student_id' => $student->id,
            'submitted_by_user_id' => $user->id,
            'status' => ApplicationStatus::DRAFT,
            'statement' => str_repeat('I need support to continue my studies in a remote area. ', 5),
        ]);
    }

    public function test_documents_page_lists_required_documents(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->get("/applications/{$application->id}/documents")
            ->assertOk()
            ->assertSee('Birth registration certificate')
            ->assertSee('Academic report / transcript')
            ->assertSee('Required')
            ->assertSee('Optional');
    }

    public function test_student_can_upload_and_download_a_document(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('birth.pdf', 120, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $document = $application->documents()->firstOrFail();
        $this->assertSame(DocumentType::BIRTH_REGISTRATION->value, $document->document_type);
        $this->assertSame('birth.pdf', $document->original_filename);
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($user)
            ->get("/applications/{$application->id}/documents/{$document->id}/download")
            ->assertOk()
            ->assertDownload('birth.pdf');
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, $application->documents()->count());
    }

    public function test_document_type_not_required_by_scholarship_is_rejected(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::CITIZENSHIP_GUARDIAN->value,
            'file' => UploadedFile::fake()->create('scan.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('document_type');
    }

    public function test_another_student_cannot_download_the_document(): void
    {
        [$owner] = $this->student();
        [$intruder] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($owner, $scholarship);

        $this->actingAs($owner)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('birth.pdf', 20, 'application/pdf'),
        ]);

        $document = $application->documents()->firstOrFail();

        $this->actingAs($intruder)
            ->get("/applications/{$application->id}/documents/{$document->id}/download")
            ->assertForbidden();

        $this->actingAs($intruder)
            ->get("/applications/{$application->id}/documents")
            ->assertForbidden();
    }

    public function test_replacing_a_document_keeps_a_single_record_and_removes_the_old_file(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('first.pdf', 10, 'application/pdf'),
        ]);

        $firstPath = $application->documents()->firstOrFail()->path;

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('second.pdf', 10, 'application/pdf'),
        ]);

        $this->assertSame(1, $application->documents()->count());
        $document = $application->documents()->firstOrFail();
        $this->assertSame('second.pdf', $document->original_filename);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_submission_requires_required_documents(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->patch("/applications/{$application->id}/submit")
            ->assertSessionHasErrors('documents');

        $this->assertSame(ApplicationStatus::DRAFT, $application->fresh()->status);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('birth.pdf', 10, 'application/pdf'),
        ]);

        $this->actingAs($user)->patch("/applications/{$application->id}/submit")
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('applications.show', $application));

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
    }

    public function test_documents_are_locked_after_submission(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('birth.pdf', 10, 'application/pdf'),
        ]);

        $this->actingAs($user)->patch("/applications/{$application->id}/submit");

        $document = $application->documents()->firstOrFail();

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::ACADEMIC_REPORT->value,
            'file' => UploadedFile::fake()->create('report.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->actingAs($user)->delete("/applications/{$application->id}/documents/{$document->id}")
            ->assertSessionHasErrors('file');

        $this->assertTrue($application->documents()->where('id', $document->id)->exists());
    }

    public function test_withdrawing_a_draft_removes_uploaded_files(): void
    {
        [$user] = $this->student();
        $scholarship = $this->scholarshipWithDocuments();
        $application = $this->draftApplication($user, $scholarship);

        $this->actingAs($user)->post("/applications/{$application->id}/documents", [
            'document_type' => DocumentType::BIRTH_REGISTRATION->value,
            'file' => UploadedFile::fake()->create('birth.pdf', 10, 'application/pdf'),
        ]);

        $path = $application->documents()->firstOrFail()->path;
        Storage::disk('local')->assertExists($path);

        $this->actingAs($user)->delete("/applications/{$application->id}")->assertRedirect(route('applications.index'));

        Storage::disk('local')->assertMissing($path);
        $this->assertSame(0, Application::query()->count());
    }
}
