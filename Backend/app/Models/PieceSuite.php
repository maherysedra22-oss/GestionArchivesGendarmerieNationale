<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PieceSuite extends Model
{
    protected $table = 'pieces_suite';

    protected $primaryKey = 'id_piece_suit';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nom_piece_suit',
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
            'id_piece_suit',
            'id_piece_suit'
        );
    }
}