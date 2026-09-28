<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\StudentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    public const STATUS_UNVERIFIED = 'UNVERIFIED';

    public const STATUS_VERIFIED = 'VERIFIED';

    protected $fillable = [
        'scholar_student_id',
        'user_id',
        'guardian_id',
        'school_id',
        'birth_registration_number',
        'name',
        'name_np',
        'date_of_birth',
        'gender',
        'education_level',
        'grade',
        'province',
        'district',
        'municipality',
        'student_category',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'gender' => Gender::class,
            'education_level' => EducationLevel::class,
            'student_category' => StudentCategory::class,
            'grade' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::STATUS_VERIFIED;
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
