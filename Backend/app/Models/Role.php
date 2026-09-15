<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_role';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_role',
        'description',
        'actif',
        'systeme',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'systeme' => 'boolean',
        ];
    }

    /**
     * Utilisateurs possédant ce rôle.
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(
            Utilisateur::class,
            'id_role',
            'id_role'
        );
    }

    /**
     * Permissions associées à ce rôle.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'id_role',
            'id_permission'
        );
    }
}