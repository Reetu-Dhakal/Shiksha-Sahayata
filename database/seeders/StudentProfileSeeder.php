<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\StudentCategory;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Database\Seeder;

/**
 * FICTIONAL DEMO DATA — all student and guardian details are invented.
 */
class StudentProfileSeeder extends Seeder
{
    public function run(): void
    {
        $studentService = app(StudentService::class);

        $studentUser = User::query()->where('email', 'aasha.student@mail.com')->first();
        $guardianUser = User::query()->where('email', 'guardian.aasha@mail.com')->first();

        if ($studentUser === null || $guardianUser === null) {
            return;
        }

        $guardian = Guardian::query()->firstOrCreate(
            ['user_id' => $guardianUser->id],
            [
                'name' => 'Aasha Guardian',
                'relationship' => 'Mother',
                'phone' => '9800000305',
                'citizenship_number' => null,
                'address' => 'Ward No. 5, Dhulikhel, Kavrepalanchok',
            ]
        );

        if (Student::query()->where('user_id', $studentUser->id)->exists()) {
            return;
        }

        $school = School::query()->where('school_code', 'SCH-KAV-001')->first();

        Student::query()->create([
            'scholar_student_id' => $studentService->generateScholarStudentId(),
            'user_id' => $studentUser->id,
            'guardian_id' => $guardian->id,
            'school_id' => $school?->id,
            'birth_registration_number' => 'BR-2055-00123',
            'name' => 'Aasha Student',
            'name_np' => 'आशा विद्यार्थी',
            'date_of_birth' => '2010-03-14',
            'gender' => Gender::FEMALE,
            'education_level' => EducationLevel::SECONDARY,
            'grade' => 10,
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'student_category' => StudentCategory::LOW_INCOME,
            'verification_status' => Student::STATUS_UNVERIFIED,
        ]);
    }
}
