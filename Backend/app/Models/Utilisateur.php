<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'utilisateurs';

    protected $primaryKey = 'id_utilisateur';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'poste_fonction',
        'email',
        'mot_de_passe',
        'id_grade',
        'id_role',
        'statut',
        'derniere_connexion',
        'doit_changer_mdp',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'statut' => 'boolean',
            'derniere_connexion' => 'datetime',
            'doit_changer_mdp' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'id_role',
            'id_role'
        );
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(
            GradeMilitaire::class,
            'id_grade',
            'id_grade'
        );
    }


    //Vérifie si l'utilisateur possède une permission donnée.
    
    public function hasPermission(string $codePermission): bool
    {
        return $this->statut
            && $this->role
            && $this->role->actif
            && $this->role->permissions()
                ->where('code_permission', $codePermission)
                ->where('actif', true)
                ->exists();
    }

    public function journaux(): HasMany
    {
        return $this->hasMany(
            JournalActivite::class,
            'id_utilisateur',
            'id_utilisateur'
        );
    }

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }
}