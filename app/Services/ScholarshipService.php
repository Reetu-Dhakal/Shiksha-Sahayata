<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScholarshipService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $admin, array $data): Scholarship
    {
        return DB::transaction(function () use ($admin, $data) {
            $scholarship = Scholarship::query()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'provider' => $data['provider'],
                'application_start' => $data['application_start'],
                'application_deadline' => $data['application_deadline'],
                'education_level' => $data['education_level'] ?? null,
                'target_grade_min' => $data['target_grade_min'] ?? null,
                'target_grade_max' => $data['target_grade_max'] ?? null,
                'available_slots' => $data['available_slots'] ?? null,
                'status' => ScholarshipStatus::DRAFT,
                'created_by' => $admin->id,
            ]);

            $this->syncChildren($scholarship, $data);

            return $scholarship;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Scholarship $scholarship, array $data): Scholarship
    {
        return DB::transaction(function () use ($scholarship, $data) {
            $scholarship->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'provider' => $data['provider'],
                'application_start' => $data['application_start'],
                'application_deadline' => $data['application_deadline'],
                'education_level' => $data['education_level'] ?? null,
                'target_grade_min' => $data['target_grade_min'] ?? null,
                'target_grade_max' => $data['target_grade_max'] ?? null,
                'available_slots' => $data['available_slots'] ?? null,
            ]);

            $this->syncChildren($scholarship, $data);

            $scholarship->refresh();

            if ($scholarship->status === ScholarshipStatus::PUBLISHED) {
                $this->assertPublishable($scholarship);
            }

            return $scholarship;
        });
    }

    /**
     * Apply a status change with controlled transitions.
     *
     * @throws ValidationException
     */
    public function changeStatus(Scholarship $scholarship, ScholarshipStatus $target): void
    {
        $current = $scholarship->status;

        $allowed = match ($target) {
            ScholarshipStatus::PUBLISHED => [ScholarshipStatus::DRAFT, ScholarshipStatus::CLOSED],
            ScholarshipStatus::CLOSED => [ScholarshipStatus::PUBLISHED],
            ScholarshipStatus::COMPLETED => [ScholarshipStatus::PUBLISHED, ScholarshipStatus::CLOSED],
            default => [],
        };

        if ($current === $target) {
            return;
        }

        if (! in_array($current, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => sprintf('A scholarship cannot move from %s to %s.', $current->label(), $target->label()),
            ]);
        }

        if ($target === ScholarshipStatus::PUBLISHED) {
            $this->assertPublishable($scholarship);
        }

        $scholarship->update(['status' => $target]);
    }

    /**
     * @throws ValidationException
     */
    private function assertPublishable(Scholarship $scholarship): void
    {
        $scholarship->load(['criteria', 'requiredDocuments']);

        if ($scholarship->application_deadline->startOfDay()->lt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'application_deadline' => 'A scholarship cannot be published after its application deadline.',
            ]);
        }

        if ($scholarship->application_start->startOfDay()->gt($scholarship->application_deadline->startOfDay())) {
            throw ValidationException::withMessages([
                'application_deadline' => 'The application deadline must be on or after the start date.',
            ]);
        }

        if ($scholarship->criteria->isEmpty()) {
            throw ValidationException::withMessages([
                'criteria' => 'At least one selection criterion is required before publishing.',
            ]);
        }

        if (abs($scholarship->totalWeight() - 100.0) > 0.01) {
            throw ValidationException::withMessages([
                'criteria' => sprintf('Criterion weights must total 100%% before publishing (currently %.2f%%).', $scholarship->totalWeight()),
            ]);
        }

        if ($scholarship->requiredDocuments->where('is_required', true)->isEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'At least one required document must be defined before publishing.',
            ]);
        }
    }

    /**
     * Sync criteria, eligibility rules, required documents and committee members.
     *
     * @param  array<string, mixed>  $data
     */
    private function syncChildren(Scholarship $scholarship, array $data): void
    {
        if (array_key_exists('criteria', $data)) {
            $scholarship->criteria()->delete();

            foreach (array_values($data['criteria'] ?? []) as $index => $criterion) {
                $scholarship->criteria()->create([
                    'name' => $criterion['name'],
                    'description' => $criterion['description'] ?? null,
                    'weight' => $criterion['weight'],
                    'maximum_score' => $criterion['maximum_score'],
                    'order' => $index,
                ]);
            }
        }

        if (array_key_exists('documents', $data)) {
            $scholarship->requiredDocuments()->delete();

            foreach (array_values($data['documents'] ?? []) as $index => $document) {
                $scholarship->requiredDocuments()->create([
                    'document_type' => $document['document_type'],
                    'description' => $document['description'] ?? null,
                    'is_required' => (bool) ($document['is_required'] ?? false),
                    'order' => $index,
                ]);
            }
        }

        if (array_key_exists('rules', $data)) {
            $scholarship->eligibilityRules()->delete();

            foreach (array_values($data['rules'] ?? []) as $index => $rule) {
                $scholarship->eligibilityRules()->create([
                    'field' => $rule['field'],
                    'operator' => $rule['operator'],
                    'value' => $rule['value'],
                    'description' => $rule['description'] ?? null,
                    'order' => $index,
                ]);
            }
        }

        if (array_key_exists('committee', $data)) {
            $memberIds = User::query()
                ->where('role', Role::COMMITTEE)
                ->whereIn('id', array_values($data['committee'] ?? []))
                ->pluck('id')
                ->all();

            $scholarship->committeeMembers()->delete();

            foreach ($memberIds as $userId) {
                $scholarship->committeeMembers()->create(['user_id' => $userId]);
            }
        }
    }
}
