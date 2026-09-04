<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourrierArrive extends Model
{
    use SoftDeletes;

    protected $table = 'courriers_arrives';

    protected $primaryKey = 'num_enreg_courr_arr';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'reference',
        'date_enreg',
        'num_ordre_orig',
        'lib_orig',
        'objet_courr_arri',
        'id_piece_suit',
        'id_class',
        'id_utilisateur_creation',
        'priorite',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'date_enreg' => 'date',
        ];
    }

    public function pieceSuite(): BelongsTo
    {
        return $this->belongsTo(
            PieceSuite::class,
            'id_piece_suit',
            'id_piece_suit'
        );
    }

    public function classement(): BelongsTo
    {
        return $this->belongsTo(
            Classement::class,
            'id_class',
            'id_class'
        );
    }

    public function utilisateurCreation(): BelongsTo
    {
        return $this->belongsTo(
            Utilisateur::class,
            'id_utilisateur_creation',
            'id_utilisateur'
        );
    }

    public function documents(): BelongsToMany
    {
    return $this->belongsToMany(
        DocumentNumerique::class,
        'documents_courriers_arrives',
        'num_enreg_courr_arr',
        'num_doc'
    );
    }
}