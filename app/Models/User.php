<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_INACTIVE = 'INACTIVE';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasRole(Role|string ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->role === ($role instanceof Role ? $role->value : $role)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isApplicant(): bool
    {
        return $this->role->isApplicant();
    }

    /**
     * The student profile owned by this account.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * The guardian profile owned by this account.
     */
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * School jurisdiction (school officers).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Local education unit jurisdiction (local education officers).
     */
    public function localEducationUnit(): BelongsTo
    {
        return $this->belongsTo(LocalEducationUnit::class);
    }
}
