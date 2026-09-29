<?php

namespace App\Models;

use App\Enums\EligibilityField;
use App\Enums\EligibilityOperator;
use App\Enums\Gender;
use App\Enums\StudentCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibilityRule extends Model
{
    use HasFactory;

    protected $table = 'scholarship_eligibility_rules';

    protected $fillable = [
        'scholarship_id',
        'field',
        'operator',
        'value',
        'description',
        'description_np',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'field' => EligibilityField::class,
            'operator' => EligibilityOperator::class,
            'order' => 'integer',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value): ?string => $value === null && ($this->attributes['description_np'] ?? null) === null
            ? null
            : pick_translation($value, $this->attributes['description_np'] ?? null));
    }

    /**
     * Human readable description of this rule.
     */
    public function label(): string
    {
        $description = $this->description;
        $hasTranslation = trim((string) ($this->attributes['description_np'] ?? '')) !== '';

        if ($description !== null && (app()->getLocale() !== 'np' || $hasTranslation)) {
            return $description;
        }

        $params = [
            'field' => $this->field->label(),
            'value' => $this->localizedValue(),
        ];

        return $this->operator === EligibilityOperator::IN
            ? __('common.rule_in', $params)
            : __('common.rule_equals', $params);
    }

    /**
     * Stored values such as FEMALE or DALIT are shown as translated labels.
     */
    private function localizedValue(): string
    {
        return match ($this->field) {
            EligibilityField::GENDER => implode(', ', array_map(
                fn (string $value): string => Gender::tryFrom(trim($value))?->label() ?? trim($value),
                $this->values(),
            )),
            EligibilityField::STUDENT_CATEGORY => implode(', ', array_map(
                fn (string $value): string => StudentCategory::tryFrom(trim($value))?->label() ?? trim($value),
                $this->values(),
            )),
            default => $this->value,
        };
    }

    /**
     * @return list<string>
     */
    public function values(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->value)), fn ($v) => $v !== ''));
    }
}
