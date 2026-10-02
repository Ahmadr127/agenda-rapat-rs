<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * Nama role bawaan (data awal RbacSeeder). Satu-satunya tempat
     * nama role disebut di kode; selebihnya memakai permission.
     */
    public const SYSTEM_SUPERADMIN = 'Superadmin';
    public const SYSTEM_OPERATOR = 'Operator Unit';
    public const SYSTEM_VIEWER = 'Viewer / Pimpinan';

    protected $fillable = ['name', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
