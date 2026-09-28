<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public function record(
        string $action,
        string $description,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
        ?User $actor = null,
    ): AuditLog {
        $actor ??= auth()->user();

        return AuditLog::query()->create([
            'user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => str(request()->userAgent() ?? '')->limit(240, '')->value(),
            'old_values' => $old !== [] ? $old : null,
            'new_values' => $new !== [] ? $new : null,
            'created_at' => now(),
        ]);
    }
}
