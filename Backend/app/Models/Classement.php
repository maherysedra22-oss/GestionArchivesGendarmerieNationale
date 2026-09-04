<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classement extends Model
{
    protected $table = 'classements';

    protected $primaryKey = 'id_class';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_class',
        'type_courrier',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function courriersArrives(): HasMany
    {
        return $this->hasMany(
            CourrierArrive::class,
            'id_class',
            'id_class'
        );
    }

    public function courriersDepart(): HasMany
    {
        return $this->hasMany(
            CourrierDepart::class,
            'id_class',
            'id_class'
        );
    }
}