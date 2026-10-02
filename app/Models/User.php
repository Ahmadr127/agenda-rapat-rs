<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole(string ...$names): bool
    {
        return $this->roles->pluck('name')->intersect($names)->isNotEmpty();
    }

    /**
     * Cek izin lewat role yang dimiliki. TIDAK pernah membandingkan
     * nama role di sini — pemetaan role→permission hidup di database.
     * Bila tabel RBAC belum termigrasi, tolak dengan aman (false)
     * alih-alih meledak 500 di setiap halaman.
     */
    public function hasPermission(string $key): bool
    {
        if (! self::rbacTablesReady()) {
            return false;
        }

        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains('key', $key);
    }

    protected static ?bool $rbacTablesReady = null;

    protected static function rbacTablesReady(): bool
    {
        if (self::$rbacTablesReady === null) {
            try {
                self::$rbacTablesReady = Schema::hasTable('roles')
                    && Schema::hasTable('permissions')
                    && Schema::hasTable('permission_role')
                    && Schema::hasTable('role_user');
            } catch (\Throwable) {
                self::$rbacTablesReady = false;
            }
        }

        return self::$rbacTablesReady;
    }

    /**
     * Unit asal user (via relasi employee). Null bila akun belum tertaut pegawai.
     */
    public function unitId(): ?int
    {
        return $this->employee?->unit_id;
    }
}
