<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeMilitaire extends Model
{
    protected $table = 'grades_militaires';

    protected $primaryKey = 'id_grade';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_grade',
        'ordre_hierarchique',
        'categorie',
        'insigne_symbole',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'ordre_hierarchique' => 'integer',
            'actif' => 'boolean',
        ];
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(
            Utilisateur::class,
            'id_grade',
            'id_grade'
        );
    }
}