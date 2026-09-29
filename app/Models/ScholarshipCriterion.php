<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipCriterion extends Model
{
    use HasFactory;

    protected $table = 'scholarship_criteria';

    protected $fillable = [
        'scholarship_id',
        'name',
        'name_np',
        'description',
        'description_np',
        'weight',
        'maximum_score',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'maximum_score' => 'integer',
            'order' => 'integer',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn ($value): string => pick_translation($value, $this->attributes['name_np'] ?? null));
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value): ?string => $value === null
            ? null
            : pick_translation($value, $this->attributes['description_np'] ?? null));
    }
}
