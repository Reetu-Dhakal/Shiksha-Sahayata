<?php

namespace App\Models;

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
        'description',
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
}
