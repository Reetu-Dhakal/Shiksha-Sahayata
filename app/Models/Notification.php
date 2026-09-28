<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'params',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'params' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function title(): string
    {
        return $this->type->title($this->params ?? []);
    }

    public function body(): string
    {
        return $this->type->body($this->params ?? []);
    }
}
