<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $table = 'scholarship_documents';

    protected $fillable = [
        'scholarship_id',
        'document_type',
        'description',
        'description_np',
        'is_required',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value): ?string => $value === null && ($this->attributes['description_np'] ?? null) === null
            ? null
            : pick_translation($value, $this->attributes['description_np'] ?? null));
    }

    public function label(): string
    {
        return $this->description ?: $this->document_type->label();
    }
}
