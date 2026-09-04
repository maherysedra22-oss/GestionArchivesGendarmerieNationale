<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourrierDepart extends Model
{
    use SoftDeletes;

    protected $table = 'courriers_depart';

    protected $primaryKey = 'num_ordre_dep';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'reference',
        'date_dep',
        'num_nat',
        'objet_courr_dep',
        'id_class',
        'id_utilisateur_creation',
        'priorite',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'date_dep' => 'date',
        ];
    }

    public function nature(): BelongsTo
    {
        return $this->belongsTo(
            NatureCourrierDepart::class,
            'num_nat',
            'num_nat'
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

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(
            Destination::class,
            'destiner',
            'num_ordre_dep',
            'id_desti'
        );
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(
            DocumentNumerique::class,
            'documents_courriers_depart',
            'num_ordre_dep',
            'num_doc'
        );
    }
}