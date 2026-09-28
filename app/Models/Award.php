<?php

namespace App\Models;

use App\Enums\AwardStatus;
use App\Enums\DisbursementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Award extends Model
{
    protected $fillable = [
        'application_id',
        'award_number',
        'verification_code',
        'status',
        'issued_by_user_id',
        'issued_at',
        'disbursement_status',
        'disbursement_remarks',
        'disbursement_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AwardStatus::class,
            'disbursement_status' => DisbursementStatus::class,
            'issued_at' => 'datetime',
            'disbursement_updated_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->status === AwardStatus::ACTIVE;
    }
}
