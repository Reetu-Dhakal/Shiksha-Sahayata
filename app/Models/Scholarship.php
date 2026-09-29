<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\ScholarshipStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_np',
        'description',
        'description_np',
        'provider',
        'provider_np',
        'application_start',
        'application_deadline',
        'education_level',
        'target_grade_min',
        'target_grade_max',
        'available_slots',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'application_start' => 'date',
            'application_deadline' => 'date',
            'education_level' => EducationLevel::class,
            'status' => ScholarshipStatus::class,
            'target_grade_min' => 'integer',
            'target_grade_max' => 'integer',
            'available_slots' => 'integer',
        ];
    }

    /**
     * The stored Nepali text is returned when the application runs in Nepali.
     * Use getRawOriginal() when the stored (English) value is required.
     */
    protected function title(): Attribute
    {
        return Attribute::get(fn ($value): ?string => pick_translation($value, $this->attributes['title_np'] ?? null));
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value): ?string => pick_translation($value, $this->attributes['description_np'] ?? null));
    }

    protected function provider(): Attribute
    {
        return Attribute::get(fn ($value): ?string => pick_translation($value, $this->attributes['provider_np'] ?? null));
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(ScholarshipCriterion::class)->orderBy('order')->orderBy('id');
    }

    public function eligibilityRules(): HasMany
    {
        return $this->hasMany(EligibilityRule::class)->orderBy('order')->orderBy('id');
    }

    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(RequiredDocument::class)->orderBy('order')->orderBy('id');
    }

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(ScholarshipCommittee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === ScholarshipStatus::PUBLISHED;
    }

    public function isAcceptingApplications(): bool
    {
        return $this->isPublished()
            && now()->startOfDay()->lte($this->application_deadline)
            && now()->startOfDay()->gte($this->application_start);
    }

    public function isExpired(): bool
    {
        return now()->startOfDay()->gt($this->application_deadline);
    }

    public function totalWeight(): float
    {
        return (float) $this->criteria->sum('weight');
    }

    public function gradeRangeLabel(): string
    {
        $min = $this->target_grade_min;
        $max = $this->target_grade_max;

        if ($min === null && $max === null) {
            return __('common.grade_all');
        }

        if ($min === null) {
            return __('common.grade_upto', ['max' => $max]);
        }

        if ($max === null) {
            return __('common.grade_from', ['min' => $min]);
        }

        return __('common.grade_range', ['min' => $min, 'max' => $max]);
    }
}
