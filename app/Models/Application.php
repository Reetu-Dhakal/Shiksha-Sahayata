<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id',
        'student_id',
        'submitted_by_user_id',
        'status',
        'is_assisted',
        'statement',
        'previous_school',
        'current_grade',
        'grade_point_average',
        'return_remarks',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'is_assisted' => 'boolean',
            'current_grade' => 'integer',
            'grade_point_average' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isSubmitted(): bool
    {
        return $this->status !== ApplicationStatus::DRAFT;
    }

    public function statusStepIndex(): int
    {
        $timeline = ApplicationStatus::timeline();
        $key = array_search($this->status, $timeline, true);

        if ($key !== false) {
            return $key;
        }

        return match ($this->status) {
            ApplicationStatus::RETURNED_FOR_CORRECTION => 0,
            ApplicationStatus::REJECTED, ApplicationStatus::APPEALED, ApplicationStatus::WAITLISTED => 3,
            default => -1,
        };
    }
}
