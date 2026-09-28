<?php

namespace App\Models;

use App\Enums\EligibilityField;
use App\Enums\EligibilityOperator;
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

    /**
     * Human readable description of this rule.
     */
    public function label(): string
    {
        if ($this->description) {
            return $this->description;
        }

        return sprintf('%s %s %s', $this->field->label(), $this->operator->label(), $this->value);
    }

    /**
     * @return list<string>
     */
    public function values(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->value)), fn ($v) => $v !== ''));
    }
}
