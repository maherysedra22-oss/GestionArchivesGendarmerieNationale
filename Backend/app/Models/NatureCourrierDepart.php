<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NatureCourrierDepart extends Model
{
    protected $table = 'natures_courriers_depart';

    protected $primaryKey = 'num_nat';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_nature',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function courriersDepart(): HasMany
    {
        return $this->hasMany(
            CourrierDepart::class,
            'num_nat',
            'num_nat'
        );
    }
}