<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriterionScore extends Model
{
    protected $fillable = [
        'application_id',
        'criterion_id',
        'scored_by_user_id',
        'score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ScholarshipCriterion::class, 'criterion_id');
    }

    public function scorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scored_by_user_id');
    }

    public function weightedValue(): float
    {
        $criterion = $this->criterion;

        if ($criterion === null || $criterion->maximum_score <= 0) {
            return 0.0;
        }

        return ((float) $this->score / (float) $criterion->maximum_score) * (float) $criterion->weight;
    }
}
