<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Enums\StudentCategory;
use App\Models\Application;
use App\Models\LocalEducationUnit;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistedApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        return School::query()->firstOrCreate(
            ['school_code' => $code],
            [
                'name' => $code.' School',
                'province' => 'Bagmati Province',
                'district' => 'Kavrepalanchok',
                'municipality' => 'Dhulikhel Municipality',
                'status' => School::STATUS_ACTIVE,
            ]
        );
    }

    private function studentFor(School $school, string $name, string $municipality = 'Dhulikhel Municipality'): array
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);

        $student = Student::query()->create([
            'scholar_student_id' => 'SS-ASST-'.uniqid(),
            'user_id' => $user->id,
            'name' => $name,
            'date_of_birth' => '2011-05-02',
            'gender' => Gender::FEMALE,
            'education_level' => EducationLevel::SECONDARY,
            'grade' => 9,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => $municipality,
            'student_category' => StudentCategory::LOW_INCOME,
            'school_id' => $school->id,
            'verification_status' => Student::STATUS_VERIFIED,
        ]);

        return [$user, $student];
    }

    private function scholarship(): Scholarship
    {
        $admin = User::factory()->admin()->create();

        $scholarship = Scholarship::query()->create([
            'title' => 'Assisted Scholarship',
            'description' => 'Used to test assisted applications.',
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

        return $scholarship;
    }

    public function test_school_officer_fills_an_assisted_application_for_their_school(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school('SCH-A')->id]);
        [$applicant, $student] = $this->studentFor($this->school('SCH-A'), 'Assisted Student');
        $scholarship = $this->scholarship();

        $this->actingAs($officer)->get(route('applications.create'))
            ->assertOk()
            ->assertSee('New assisted application')
            ->assertSee($student->name);

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student needs support to continue studying. ', 4),
            'current_grade' => 9,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $application = Application::query()->latest('id')->first();
        $this->assertNotNull($application);
        $this->assertTrue($application->is_assisted);
        $this->assertSame($student->id, $application->student_id);

        $this->actingAs($officer)->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Assisted Applications')
            ->assertSee('Assisted Student');

        $this->actingAs($applicant)->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Assisted Student');
    }

    public function test_school_officer_cannot_assist_a_student_from_another_school(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school('SCH-A')->id]);
        [, $otherStudent] = $this->studentFor($this->school('SCH-B'), 'Other School Student');
        $scholarship = $this->scholarship();

        $before = Application::query()->count();

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $otherStudent->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('Trying to reach a student outside the jurisdiction. ', 4),
        ])->assertSessionHasErrors('student_id');

        $this->assertSame($before, Application::query()->count());
    }

    public function test_local_officer_is_limited_to_their_jurisdiction(): void
    {
        $unit = LocalEducationUnit::query()->create([
            'name' => 'Dhulikhel Local Education Unit',
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'status' => 'ACTIVE',
        ]);

        $officer = User::factory()->create([
            'role' => Role::LOCAL_OFFICER,
            'local_education_unit_id' => $unit->id,
        ]);

        [, $inJurisdiction] = $this->studentFor($this->school('SCH-A'), 'In Jurisdiction Student');
        [, $outside] = $this->studentFor($this->school('SCH-A'), 'Outside Student', 'Banepa Municipality');
        $scholarship = $this->scholarship();

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $outside->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student lives outside my municipality. ', 5),
        ])->assertSessionHasErrors('student_id');

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $inJurisdiction->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student lives inside my municipality. ', 5),
        ])->assertSessionHasNoErrors();
    }

    public function test_officer_can_submit_an_assisted_draft_application(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school('SCH-A')->id]);
        [, $student] = $this->studentFor($this->school('SCH-A'), 'Submit Assisted Student');
        $scholarship = $this->scholarship();

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student needs support to continue studying. ', 4),
        ]);

        $application = Application::query()->latest('id')->first();

        $this->actingAs($officer)->patch(route('applications.submit', $application))
            ->assertSessionHasNoErrors();

        $this->assertSame(ApplicationStatus::SUBMITTED, $application->fresh()->status);
        $this->assertSame($officer->id, $application->fresh()->submitted_by_user_id);
    }

    public function test_admin_sees_assisted_applications_from_every_school(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school('SCH-A')->id]);
        [, $student] = $this->studentFor($this->school('SCH-A'), 'Nationwide Assisted Student');
        $scholarship = $this->scholarship();

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student needs support to continue studying. ', 4),
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('applications.index'))
            ->assertOk()
            ->assertSee('Assisted Applications')
            ->assertSee('Nationwide Assisted Student');
    }

    public function test_committee_members_cannot_use_application_routes(): void
    {
        $committee = User::factory()->committee()->create();

        $this->actingAs($committee)->get(route('applications.index'))->assertForbidden();
        $this->actingAs($committee)->get(route('applications.create'))->assertForbidden();
    }

    public function test_students_do_not_see_other_students_assisted_applications(): void
    {
        $officer = User::factory()->create(['role' => Role::SCHOOL_OFFICER, 'school_id' => $this->school('SCH-A')->id]);
        [, $student] = $this->studentFor($this->school('SCH-A'), 'Private Assisted Student');
        $scholarship = $this->scholarship();

        $this->actingAs($officer)->post(route('applications.store'), [
            'student_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'statement' => str_repeat('This student needs support to continue studying. ', 4),
        ]);

        [, $bystander] = $this->studentFor($this->school('SCH-A'), 'Bystander Student');
        $bystanderUser = User::query()->whereHas('student', fn ($query) => $query->where('id', $bystander->id))->first();

        $this->actingAs($bystanderUser)->get(route('applications.index'))
            ->assertOk()
            ->assertDontSee('Private Assisted Student');
    }
}
