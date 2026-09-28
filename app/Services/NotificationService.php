<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notify(User $user, NotificationType $type, array $params = [], ?string $link = null): Notification
    {
        return Notification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'params' => $params,
            'link' => $link,
        ]);
    }

    /**
     * @param  iterable<User|int>  $recipients
     * @param  array<string, mixed>  $params
     */
    public function notifyMany(iterable $recipients, NotificationType $type, array $params = [], ?string $link = null): void
    {
        $rows = collect($recipients)
            ->map(fn ($recipient): int => $recipient instanceof User ? $recipient->id : (int) $recipient)
            ->unique()
            ->map(fn (int $userId): array => [
                'user_id' => $userId,
                'type' => $type->value,
                'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'link' => $link,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            Notification::query()->insert($rows);
        }
    }

    public function forUser(User $user): Collection
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function markAllRead(User $user): void
    {
        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markRead(User $user, Notification $notification): void
    {
        if ($notification->user_id === $user->id && $notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }
    }
}
