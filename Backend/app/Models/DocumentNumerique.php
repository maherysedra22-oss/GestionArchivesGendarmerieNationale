<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentNumerique extends Model
{
    use SoftDeletes;

    protected $table = 'documents_numeriques';

    protected $primaryKey = 'num_doc';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nom_original',
        'nom_stockage',
        'extension',
        'type_mime',
        'taille',
        'chemin',
        'checksum_sha256',
        'id_utilisateur_upload',
    ];

    protected $casts = [
        'taille' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Utilisateur qui a uploadé le document.
     */
    public function utilisateurUpload(): BelongsTo
    {
        return $this->belongsTo(
            Utilisateur::class,
            'id_utilisateur_upload',
            'id_utilisateur'
        );
    }

    /**
     * Courriers arrivés associés.
     */
    public function courriersArrives(): BelongsToMany
    {
        return $this->belongsToMany(
            CourrierArrive::class,
            'documents_courriers_arrives',
            'num_doc',
            'num_enreg_courr_arr'
        );
    }

    /**
     * Courriers départ associés.
     */
    public function courriersDepart(): BelongsToMany
    {
        return $this->belongsToMany(
            CourrierDepart::class,
            'documents_courriers_depart',
            'num_doc',
            'num_ordre_dep'
        );
    }
}