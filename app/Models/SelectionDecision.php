<?php

namespace App\Models;

use App\Enums\Decision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelectionDecision extends Model
{
    protected $fillable = [
        'application_id',
        'decision',
        'decided_by_user_id',
        'reason',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => Decision::class,
            'decided_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
