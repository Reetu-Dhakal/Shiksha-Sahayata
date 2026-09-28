<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\EligibilityField;
use App\Enums\EligibilityOperator;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * FICTIONAL DEMO DATA — scholarship titles, providers and rules are invented for testing.
 */
class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $committee = User::query()->where('email', 'committee@shikshasahayata.np')->first();

        $merit = Scholarship::query()->firstOrCreate(
            ['title' => 'Merit-cum-Means Scholarship'],
            [
                'description' => 'Supports secondary-level students from low-income households who show academic promise. Selection is based on the published weighted criteria.',
                'provider' => 'Shiksha Sahayat Fund',
                'application_start' => now()->subDays(7),
                'application_deadline' => now()->addDays(30),
                'education_level' => EducationLevel::SECONDARY,
                'target_grade_min' => 9,
                'target_grade_max' => 10,
                'available_slots' => 5,
                'status' => ScholarshipStatus::PUBLISHED,
                'created_by' => $admin->id,
            ]
        );

        if ($merit->criteria()->count() === 0) {
            $merit->criteria()->createMany([
                ['name' => 'Economic condition', 'description' => 'Household income and dependants', 'weight' => 40, 'maximum_score' => 100, 'order' => 0],
                ['name' => 'Academic performance', 'description' => 'Recent examination results', 'weight' => 35, 'maximum_score' => 100, 'order' => 1],
                ['name' => 'Remote location', 'description' => 'Distance and accessibility of the locality', 'weight' => 15, 'maximum_score' => 100, 'order' => 2],
                ['name' => 'Inclusion category', 'description' => 'Published inclusion categories', 'weight' => 10, 'maximum_score' => 100, 'order' => 3],
            ]);

            $merit->eligibilityRules()->createMany([
                ['field' => EligibilityField::STUDENT_CATEGORY, 'operator' => EligibilityOperator::IN, 'value' => 'DALIT, LOW_INCOME, JANAJATI, REMOTE_AREA', 'description' => null, 'order' => 0],
            ]);

            $merit->requiredDocuments()->createMany([
                ['document_type' => DocumentType::BIRTH_REGISTRATION, 'description' => 'Scan of birth registration certificate', 'is_required' => true, 'order' => 0],
                ['document_type' => DocumentType::INCOME_CERTIFICATE, 'description' => 'Ward office income certificate', 'is_required' => true, 'order' => 1],
                ['document_type' => DocumentType::SCHOOL_VERIFICATION, 'description' => 'Signed school verification letter', 'is_required' => true, 'order' => 2],
                ['document_type' => DocumentType::ACADEMIC_REPORT, 'description' => 'Last academic report', 'is_required' => false, 'order' => 3],
            ]);
        }

        if ($committee !== null) {
            $merit->committeeMembers()->firstOrCreate(['user_id' => $committee->id]);
        }

        $remote = Scholarship::query()->firstOrCreate(
            ['title' => 'Remote Area Girls Scholarship'],
            [
                'description' => 'Encourages girls from remote and rural areas to continue secondary education. Selection follows the published weighted criteria.',
                'provider' => 'Himal Education Trust',
                'application_start' => now()->subDays(3),
                'application_deadline' => now()->addDays(45),
                'education_level' => EducationLevel::SECONDARY,
                'target_grade_min' => 6,
                'target_grade_max' => 10,
                'available_slots' => 10,
                'status' => ScholarshipStatus::PUBLISHED,
                'created_by' => $admin->id,
            ]
        );

        if ($remote->criteria()->count() === 0) {
            $remote->criteria()->createMany([
                ['name' => 'Academic performance', 'description' => 'Recent examination results', 'weight' => 40, 'maximum_score' => 100, 'order' => 0],
                ['name' => 'Remote location', 'description' => 'Distance and accessibility of the locality', 'weight' => 30, 'maximum_score' => 100, 'order' => 1],
                ['name' => 'Economic condition', 'description' => 'Household income and dependants', 'weight' => 30, 'maximum_score' => 100, 'order' => 2],
            ]);

            $remote->eligibilityRules()->createMany([
                ['field' => EligibilityField::GENDER, 'operator' => EligibilityOperator::EQUALS, 'value' => 'FEMALE', 'description' => 'Open to female students', 'order' => 0],
                ['field' => EligibilityField::DISTRICT, 'operator' => EligibilityOperator::IN, 'value' => 'Jumla, Bardiya, Dolakha, Sindhupalchok, Kavrepalanchok', 'description' => null, 'order' => 1],
            ]);

            $remote->requiredDocuments()->createMany([
                ['document_type' => DocumentType::BIRTH_REGISTRATION, 'description' => 'Scan of birth registration certificate', 'is_required' => true, 'order' => 0],
                ['document_type' => DocumentType::SCHOOL_VERIFICATION, 'description' => 'Signed school verification letter', 'is_required' => true, 'order' => 1],
            ]);
        }

        if ($committee !== null) {
            $remote->committeeMembers()->firstOrCreate(['user_id' => $committee->id]);
        }

        Scholarship::query()->firstOrCreate(
            ['title' => 'Community Youth Support Scholarship (Draft)'],
            [
                'description' => 'A draft scholarship used to demonstrate the drafting and publishing workflow. It is not visible to students.',
                'provider' => 'Janajyoti Education Trust',
                'application_start' => now()->addDays(7),
                'application_deadline' => now()->addDays(60),
                'education_level' => EducationLevel::SECONDARY,
                'target_grade_min' => 8,
                'target_grade_max' => 10,
                'available_slots' => null,
                'status' => ScholarshipStatus::DRAFT,
                'created_by' => $admin->id,
            ]
        );
    }
}
