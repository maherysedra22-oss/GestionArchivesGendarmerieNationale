<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destination extends Model
{
    protected $table = 'destinations';

    protected $primaryKey = 'id_desti';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'lib_officiel_desti',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function courriersDepart(): BelongsToMany
    {
        return $this->belongsToMany(
            CourrierDepart::class,
            'destiner',
            'id_desti',
            'num_ordre_dep'
        );
    }
}