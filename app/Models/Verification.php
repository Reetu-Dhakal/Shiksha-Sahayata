<?php

namespace App\Models;

use App\Enums\VerificationStage;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    protected $fillable = [
        'application_id',
        'stage',
        'officer_user_id',
        'status',
        'remarks',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'stage' => VerificationStage::class,
            'status' => VerificationStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_user_id');
    }
}
