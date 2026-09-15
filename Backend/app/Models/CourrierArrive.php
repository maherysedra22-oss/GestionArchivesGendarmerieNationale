<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourrierArrive extends Model
{
    use SoftDeletes;

    /**
     * Nom de la table
     */
    protected $table = 'courriers_arrives';


    /**
     * Clé primaire
     */
    protected $primaryKey = 'num_enreg_courr_arr';


    /**
     * La clé primaire est auto-incrémentée
     */
    public $incrementing = true;


    /**
     * Type de la clé primaire
     */
    protected $keyType = 'int';


    /**
     * Colonnes timestamps personnalisées
     */
    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';


    /**
     * Champs autorisés pour create/update
     */
    protected $fillable = [

        'date_enreg',

        'num_ordre_orig',

        'lib_orig',

        'objet_courr_arri',

        'id_piece_suit',

        'id_utilisateur_creation',

        'priorite',

        'statut_dossier',

    ];


    /**
     * Conversion automatique des types
     */
    protected $casts = [

        'date_enreg' => 'date',

        'created_at' => 'datetime',

        'updated_at' => 'datetime',

        'deleted_at' => 'datetime',

    ];


    /**
     * Pièce de suite associée au courrier
     */
    public function pieceSuite(): BelongsTo
    {
        return $this->belongsTo(

            PieceSuite::class,

            'id_piece_suit',

            'id_piece_suit'

        );
    }


    /**
     * Utilisateur ayant créé le courrier
     */
    public function utilisateurCreation(): BelongsTo
    {
        return $this->belongsTo(

            Utilisateur::class,

            'id_utilisateur_creation',

            'id_utilisateur'

        );
    }


    /**
     * Documents numériques associés au courrier
     */
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