<?php

namespace App\Models;

use App\Enums\AppealStatus;
use App\Enums\Decision;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * @var array<string, string>
     */
    private static array $scholarshipTitleCache = [];

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
        return $this->type->title($this->renderParams());
    }

    public function body(): string
    {
        return $this->type->body($this->renderParams());
    }

    /**
     * Params are stored as stable values (English titles, enum values) and are
     * rendered in the language of the reader.
     *
     * @return array<string, mixed>
     */
    private function renderParams(): array
    {
        $params = $this->params ?? [];

        if (isset($params['scholarship']) && is_string($params['scholarship'])) {
            $params['scholarship'] = self::localizeScholarshipTitle($params['scholarship']);
        }

        foreach (['decision' => Decision::class, 'outcome' => AppealStatus::class] as $key => $enum) {
            if (isset($params[$key]) && is_string($params[$key])) {
                $params[$key] = $enum::tryFrom($params[$key])?->label() ?? $params[$key];
            }
        }

        return $params;
    }

    private static function localizeScholarshipTitle(string $title): string
    {
        if (app()->getLocale() !== 'np') {
            return $title;
        }

        if (isset(self::$scholarshipTitleCache[$title])) {
            return self::$scholarshipTitleCache[$title];
        }

        $scholarship = Scholarship::query()->where('title', $title)->first();

        return self::$scholarshipTitleCache[$title] = $scholarship?->title ?? $title;
    }
}
