<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalEducationUnit extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_INACTIVE = 'INACTIVE';

    protected $table = 'local_education_units';

    protected $fillable = [
        'name',
        'province',
        'district',
        'municipality',
        'contact_information',
        'status',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function officers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
