<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function baseData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Merit Scholarship 2026',
            'description' => 'A scholarship for deserving students.',
            'provider' => 'Ministry Demo Provider',
            'application_start' => now()->toDateString(),
            'application_deadline' => now()->addDays(45)->toDateString(),
            'education_level' => 'SECONDARY',
            'target_grade_min' => 9,
            'target_grade_max' => 10,
            'available_slots' => 10,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function publishableChildren(): array
    {
        return [
            'criteria' => [
                ['name' => 'Economic condition', 'description' => null, 'weight' => 60, 'maximum_score' => 100],
                ['name' => 'Academic performance', 'description' => null, 'weight' => 40, 'maximum_score' => 100],
            ],
            'documents' => [
                ['document_type' => 'BIRTH_REGISTRATION', 'description' => 'Birth certificate', 'is_required' => '1'],
            ],
            'rules' => [
                ['field' => 'STUDENT_CATEGORY', 'operator' => 'IN', 'value' => 'DALIT, LOW_INCOME', 'description' => null],
            ],
            'committee' => [],
        ];
    }

    public function test_admin_can_create_a_draft_scholarship(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/scholarships', $this->baseData());

        $scholarship = Scholarship::query()->firstOrFail();
        $response->assertRedirect(route('admin.scholarships.edit', $scholarship));
        $this->assertSame(ScholarshipStatus::DRAFT, $scholarship->status);
        $this->assertSame($scholarship->id, $scholarship->creator->id);
    }

    public function test_admin_can_view_create_and_edit_forms(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/scholarships')->assertOk()->assertSee('Scholarship Management');
        $this->actingAs($admin)->get('/admin/scholarships/create')->assertOk()->assertSee('Create scholarship');

        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)
            ->get("/admin/scholarships/{$scholarship->id}/edit")
            ->assertOk()
            ->assertSee($scholarship->title);
    }

    public function test_non_admin_cannot_manage_scholarships(): void
    {
        $student = User::factory()->create(['role' => Role::STUDENT]);

        $this->actingAs($student)->get('/admin/scholarships')->assertForbidden();
        $this->actingAs($student)->post('/admin/scholarships', $this->baseData())->assertForbidden();
        $this->assertSame(0, Scholarship::query()->count());
    }

    public function test_scholarship_deadline_must_be_on_or_after_start_date(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/scholarships', $this->baseData([
                'application_start' => now()->addDays(10)->toDateString(),
                'application_deadline' => now()->toDateString(),
            ]))
            ->assertSessionHasErrors('application_deadline');
    }

    public function test_grade_range_must_be_ordered(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/scholarships', $this->baseData([
                'target_grade_min' => 10,
                'target_grade_max' => 8,
            ]))
            ->assertSessionHasErrors('target_grade_max');
    }

    public function test_admin_can_configure_criteria_documents_rules_and_committee(): void
    {
        $admin = $this->admin();
        $committee = User::factory()->committee()->create();

        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)->put("/admin/scholarships/{$scholarship->id}", array_merge(
            $this->baseData(),
            $this->publishableChildren(),
            ['committee' => [$committee->id]]
        ))->assertRedirect(route('admin.scholarships.edit', $scholarship));

        $scholarship->refresh()->load(['criteria', 'requiredDocuments', 'eligibilityRules', 'committeeMembers']);
        $this->assertCount(2, $scholarship->criteria);
        $this->assertSame(100.0, $scholarship->totalWeight());
        $this->assertCount(1, $scholarship->requiredDocuments);
        $this->assertCount(1, $scholarship->eligibilityRules);
        $this->assertSame($committee->id, $scholarship->committeeMembers->first()->user_id);
    }

    public function test_scholarship_cannot_publish_without_valid_configuration(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$scholarship->id}/status", ['status' => 'PUBLISHED'])
            ->assertSessionHasErrors('criteria');

        $this->assertSame(ScholarshipStatus::DRAFT, $scholarship->fresh()->status);
    }

    public function test_scholarship_cannot_publish_with_weights_not_totalling_100(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)->put("/admin/scholarships/{$scholarship->id}", array_merge(
            $this->baseData(),
            [
                'criteria' => [
                    ['name' => 'Economic condition', 'description' => null, 'weight' => 30, 'maximum_score' => 100],
                ],
                'documents' => [
                    ['document_type' => 'BIRTH_REGISTRATION', 'description' => null, 'is_required' => '1'],
                ],
            ]
        ));

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$scholarship->id}/status", ['status' => 'PUBLISHED'])
            ->assertSessionHasErrors('criteria');
    }

    public function test_scholarship_publishes_when_properly_configured(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)->put("/admin/scholarships/{$scholarship->id}", array_merge(
            $this->baseData(),
            $this->publishableChildren()
        ));

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$scholarship->id}/status", ['status' => 'PUBLISHED'])
            ->assertSessionHas('status');

        $this->assertSame(ScholarshipStatus::PUBLISHED, $scholarship->fresh()->status);
        $this->assertTrue($scholarship->fresh()->isPublished());
    }

    public function test_expired_scholarship_cannot_be_published(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData([
            'application_start' => now()->subDays(30)->toDateString(),
            'application_deadline' => now()->subDay()->toDateString(),
        ]));
        $scholarship = Scholarship::query()->firstOrFail();

        $this->actingAs($admin)->put("/admin/scholarships/{$scholarship->id}", array_merge(
            $this->baseData([
                'application_start' => now()->subDays(30)->toDateString(),
                'application_deadline' => now()->subDay()->toDateString(),
            ]),
            $this->publishableChildren()
        ));

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$scholarship->id}/status", ['status' => 'PUBLISHED'])
            ->assertSessionHasErrors('application_deadline');
    }

    public function test_invalid_status_transitions_are_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $scholarship->update(['status' => ScholarshipStatus::COMPLETED]);

        $this->actingAs($admin)
            ->patch("/admin/scholarships/{$scholarship->id}/status", ['status' => 'PUBLISHED'])
            ->assertSessionHasErrors('status');

        $this->assertSame(ScholarshipStatus::COMPLETED, $scholarship->fresh()->status);
    }

    public function test_draft_scholarship_is_not_accepting_applications(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/scholarships', $this->baseData());
        $scholarship = Scholarship::query()->firstOrFail();

        $this->assertFalse($scholarship->isAcceptingApplications());

        $scholarship->update(['status' => ScholarshipStatus::PUBLISHED]);
        $this->assertTrue($scholarship->fresh()->isAcceptingApplications());

        $scholarship->fresh()->update(['application_deadline' => now()->subDay()]);
        $this->assertFalse($scholarship->fresh()->isAcceptingApplications());
    }
}
