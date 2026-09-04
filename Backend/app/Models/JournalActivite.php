<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalActivite extends Model
{
    protected $table = 'journal_activites';

    protected $primaryKey = 'id_journal';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_utilisateur',
        'nom_utilisateur',
        'action',
        'table_concernee',
        'id_enregistrement',
        'reference_objet',
        'donnees_avant',
        'donnees_apres',
        'adresse_ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'donnees_avant' => 'array',
            'donnees_apres' => 'array',
            'id_enregistrement' => 'integer',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(
            Utilisateur::class,
            'id_utilisateur',
            'id_utilisateur'
        );
    }
}