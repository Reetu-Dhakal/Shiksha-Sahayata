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
 * English columns hold the default text, *_np columns hold the Nepali text.
 */
class ScholarshipSeeder extends Seeder
{
    /**
     * Nepali text for the demo scholarships, keyed by the English title.
     *
     * @var array<string, array{title: string, description: string, provider: string}>
     */
    private array $scholarshipNp = [
        'Merit-cum-Means Scholarship' => [
            'title' => 'मेधा र आयअनुसारको छात्रवृत्ति',
            'description' => 'उच्च शैक्षिक सम्भावना रहेका निम्न आम्दानी भएका परिवारका माध्यमिक तहका विद्यार्थीलाई सहयोग गर्छ। छनोट प्रकाशित तौलित मापदण्डमा आधारित हुन्छ।',
            'provider' => 'शिक्षा सहायता कोष',
        ],
        'Remote Area Girls Scholarship' => [
            'title' => 'दुर्गम क्षेत्रकी छात्राहरूको छात्रवृत्ति',
            'description' => 'दुर्गम र ग्रामीण क्षेत्रकी छात्राहरूलाई माध्यमिक शिक्षा जारी राख्न प्रोत्साहन गर्छ। छनोट प्रकाशित तौलित मापदण्डअनुसार हुन्छ।',
            'provider' => 'हिमाल शिक्षा ट्रस्ट',
        ],
        'Community Youth Support Scholarship (Draft)' => [
            'title' => 'सामुदायिक युवा सहयोग छात्रवृत्ति (खस्को)',
            'description' => 'मस्यौदा र प्रकाशन कार्यप्रणाली देखाउन प्रयोग हुने खस्को छात्रवृत्ति। यो विद्यार्थीहरूलाई देखिँदैन।',
            'provider' => 'जनज्योति शिक्षा ट्रस्ट',
        ],
    ];

    /**
     * Nepali text for criteria, rules and documents keyed by their English text.
     *
     * @var array<string, string>
     */
    private array $criteriaNp = [
        'Economic condition' => 'आर्थिक स्थिति',
        'Academic performance' => 'शैक्षिक प्रदर्शन',
        'Remote location' => 'दुर्गम स्थान',
        'Inclusion category' => 'समावेशी वर्ग',
    ];

    /**
     * @var array<string, string>
     */
    private array $criteriaDescriptionNp = [
        'Household income and dependants' => 'परिवारको आम्दानी र आश्रित सदस्य',
        'Recent examination results' => 'हालका परीक्षा नतिजा',
        'Distance and accessibility of the locality' => 'स्थानको दूरी र पहुँच',
        'Published inclusion categories' => 'प्रकाशित समावेशी वर्गहरू',
    ];

    /**
     * @var array<string, string>
     */
    private array $documentDescriptionNp = [
        'Scan of birth registration certificate' => 'जन्म दर्ता प्रमाणपत्रको स्क्यान',
        'Ward office income certificate' => 'वडा कार्यालयको आय प्रमाणपत्र',
        'Signed school verification letter' => 'स्वाक्षरी भएको विद्यालय प्रमाणीकरण पत्र',
        'Last academic report' => 'अन्तिम शैक्षिक प्रतिवेदन',
    ];

    /**
     * @var array<string, string>
     */
    private array $ruleDescriptionNp = [
        'Open to female students' => 'महिला विद्यार्थीहरूका लागि खुला',
    ];

    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $committee = User::query()->where('email', 'committee@shikshasahayata.np')->first();

        $merit = $this->makeScholarship(
            title: 'Merit-cum-Means Scholarship',
            attributes: [
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
            ],
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

        $this->translateChildren($merit);

        if ($committee !== null) {
            $merit->committeeMembers()->firstOrCreate(['user_id' => $committee->id]);
        }

        $remote = $this->makeScholarship(
            title: 'Remote Area Girls Scholarship',
            attributes: [
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
            ],
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

        $this->translateChildren($remote);

        if ($committee !== null) {
            $remote->committeeMembers()->firstOrCreate(['user_id' => $committee->id]);
        }

        $this->makeScholarship(
            title: 'Community Youth Support Scholarship (Draft)',
            attributes: [
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
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeScholarship(string $title, array $attributes): Scholarship
    {
        $scholarship = Scholarship::query()->firstOrCreate(['title' => $title], $attributes);

        $np = $this->scholarshipNp[$title] ?? null;

        if ($np !== null && blank($scholarship->getRawOriginal('title_np'))) {
            $scholarship->forceFill([
                'title_np' => $np['title'],
                'description_np' => $np['description'],
                'provider_np' => $np['provider'],
            ])->save();
        }

        return $scholarship;
    }

    /**
     * Fills the Nepali columns of criteria, eligibility rules and documents
     * created by an earlier run of this seeder.
     */
    private function translateChildren(Scholarship $scholarship): void
    {
        foreach ($scholarship->criteria as $criterion) {
            if (blank($criterion->getRawOriginal('name_np'))) {
                $criterion->forceFill([
                    'name_np' => $this->criteriaNp[$criterion->getRawOriginal('name')] ?? null,
                    'description_np' => $this->criteriaDescriptionNp[$criterion->getRawOriginal('description')] ?? null,
                ])->save();
            }
        }

        foreach ($scholarship->eligibilityRules as $rule) {
            if (blank($rule->getRawOriginal('description_np')) && $rule->getRawOriginal('description') !== null) {
                $rule->forceFill([
                    'description_np' => $this->ruleDescriptionNp[$rule->getRawOriginal('description')] ?? null,
                ])->save();
            }
        }

        foreach ($scholarship->requiredDocuments as $document) {
            if (blank($document->getRawOriginal('description_np'))) {
                $document->forceFill([
                    'description_np' => $this->documentDescriptionNp[$document->getRawOriginal('description')] ?? null,
                ])->save();
            }
        }
    }
}
