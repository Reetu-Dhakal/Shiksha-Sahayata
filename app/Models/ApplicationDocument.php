<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'required_document_id',
        'uploaded_by_user_id',
        'document_type',
        'original_filename',
        'path',
        'mime_type',
        'size_bytes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(RequiredDocument::class, 'required_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
