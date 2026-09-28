<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\EligibilityField;
use App\Enums\EligibilityOperator;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeScholarship(array $overrides = []): Scholarship
    {
        $admin = User::query()->first() ?? User::factory()->admin()->create();

        return Scholarship::query()->create(array_merge([
            'title' => 'Merit Scholarship for Secondary Students',
            'description' => 'Supports students from low-income households.',
            'provider' => 'Shiksha Sahayat Fund',
            'application_start' => now()->subDays(5),
            'application_deadline' => now()->addDays(20),
            'education_level' => EducationLevel::SECONDARY,
            'target_grade_min' => 9,
            'target_grade_max' => 10,
            'available_slots' => 5,
            'status' => ScholarshipStatus::PUBLISHED,
            'created_by' => $admin->id,
        ], $overrides));
    }

    public function test_guest_sees_published_scholarships_but_not_drafts(): void
    {
        $this->makeScholarship();
        $this->makeScholarship([
            'title' => 'Hidden Draft Scholarship',
            'status' => ScholarshipStatus::DRAFT,
        ]);

        $response = $this->get('/scholarships');

        $response->assertOk()
            ->assertSee('Merit Scholarship for Secondary Students')
            ->assertDontSee('Hidden Draft Scholarship');
    }

    public function test_scholarship_listing_can_be_filtered_by_keyword_and_level(): void
    {
        $this->makeScholarship(['title' => 'Rural Girls Scholarship', 'education_level' => EducationLevel::SECONDARY]);
        $this->makeScholarship(['title' => 'Bachelor Support Grant', 'education_level' => EducationLevel::BACHELOR]);

        $this->get('/scholarships?q=Rural Girls')
            ->assertOk()
            ->assertSee('Rural Girls Scholarship')
            ->assertDontSee('Bachelor Support Grant');

        $this->get('/scholarships?level=BACHELOR')
            ->assertOk()
            ->assertSee('Bachelor Support Grant')
            ->assertDontSee('Rural Girls Scholarship');
    }

    public function test_open_only_filter_excludes_expired_scholarships(): void
    {
        $this->makeScholarship(['title' => 'Open Scholarship', 'application_deadline' => now()->addDays(10)]);
        $this->makeScholarship(['title' => 'Expired Scholarship', 'application_deadline' => now()->subDays(3)]);

        $response = $this->get('/scholarships?open=1');

        $response->assertOk()
            ->assertSee('Open Scholarship')
            ->assertDontSee('Expired Scholarship');
    }

    public function test_expired_published_scholarship_is_visible_but_not_accepting(): void
    {
        $scholarship = $this->makeScholarship([
            'application_start' => now()->subDays(30),
            'application_deadline' => now()->subDays(3),
        ]);

        $this->get('/scholarships')
            ->assertOk()
            ->assertSee($scholarship->title)
            ->assertSee('Deadline passed');

        $this->assertFalse($scholarship->fresh()->isAcceptingApplications());
    }

    public function test_scholarship_detail_shows_criteria_documents_and_rules(): void
    {
        $scholarship = $this->makeScholarship();

        $scholarship->criteria()->createMany([
            ['name' => 'Economic condition', 'description' => 'Household income', 'weight' => 60, 'maximum_score' => 100, 'order' => 0],
            ['name' => 'Academic performance', 'description' => 'Recent results', 'weight' => 40, 'maximum_score' => 100, 'order' => 1],
        ]);
        $scholarship->requiredDocuments()->create([
            'document_type' => DocumentType::BIRTH_REGISTRATION,
            'description' => 'Birth certificate scan',
            'is_required' => true,
            'order' => 0,
        ]);
        $scholarship->eligibilityRules()->create([
            'field' => EligibilityField::STUDENT_CATEGORY,
            'operator' => EligibilityOperator::IN,
            'value' => 'DALIT, LOW_INCOME',
            'description' => null,
            'order' => 0,
        ]);

        $this->get("/scholarships/{$scholarship->id}")
            ->assertOk()
            ->assertSee($scholarship->title)
            ->assertSee('Economic condition')
            ->assertSee('Academic performance')
            ->assertSee('100')
            ->assertSee('Birth registration certificate')
            ->assertSee('Student category')
            ->assertSee('Accepting applications');
    }

    public function test_draft_scholarship_detail_returns_404(): void
    {
        $scholarship = $this->makeScholarship(['status' => ScholarshipStatus::DRAFT]);

        $this->get("/scholarships/{$scholarship->id}")->assertNotFound();
    }

    public function test_scholarship_cards_link_to_detail_page_and_closing_drafts_removes_them(): void
    {
        $scholarship = $this->makeScholarship();

        $this->get('/scholarships')
            ->assertOk()
            ->assertSee(route('scholarships.show', $scholarship), false);

        $scholarship->update(['status' => ScholarshipStatus::CLOSED]);

        $this->get('/scholarships')
            ->assertOk()
            ->assertSee($scholarship->title)
            ->assertSee('Closed');
    }
}
