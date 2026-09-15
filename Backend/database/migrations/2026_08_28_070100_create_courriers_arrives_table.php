<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Créer la table courriers_arrives.
     */
    public function up(): void
    {
        Schema::create('courriers_arrives', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO D'ENREGISTREMENT
            |--------------------------------------------------------------------------
            | Généré automatiquement.
            | Non saisi dans le formulaire.
            */

            $table->increments('num_enreg_courr_arr');


            /*
            |--------------------------------------------------------------------------
            | DATE D'ENREGISTREMENT
            |--------------------------------------------------------------------------
            | Date du jour automatiquement.
            */

            $table->date('date_enreg')->useCurrent();


            /*
            |--------------------------------------------------------------------------
            | NUMÉRO D'ORDRE D'ORIGINE
            |--------------------------------------------------------------------------
            | Exemple : 447/2
            */

            $table->string('num_ordre_orig', 100);


            /*
            |--------------------------------------------------------------------------
            | ORIGINE
            |--------------------------------------------------------------------------
            | Exemple : DQG/SRH
            */

            $table->string('lib_orig', 255);


            /*
            |--------------------------------------------------------------------------
            | OBJET DU COURRIER
            |--------------------------------------------------------------------------
            */

            $table->text('objet_courr_arri');


            /*
            |--------------------------------------------------------------------------
            | PIÈCE DE SUITE
            |--------------------------------------------------------------------------
            | Sélectionnée dans une Combobox.
            */

            $table->unsignedInteger('id_piece_suit');


            /*
            |--------------------------------------------------------------------------
            | UTILISATEUR DE CRÉATION
            |--------------------------------------------------------------------------
            | Rempli automatiquement avec l'utilisateur connecté.
            */

            $table->unsignedInteger('id_utilisateur_creation');


            /*
            |--------------------------------------------------------------------------
            | PRIORITÉ
            |--------------------------------------------------------------------------
            | Valeurs :
            | NORMAL
            | URGENT
            | TRES_URGENT
            */

            $table->string('priorite', 20)
                ->default('NORMAL');


            /*
            |--------------------------------------------------------------------------
            | STATUT DU DOSSIER
            |--------------------------------------------------------------------------
            | Combobox :
            |
            | En cours
            | Lecture
            | Archivé
            */

            $table->string('statut_dossier', 20)
                ->default('En cours');


            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->timestampTz('updated_at')
                ->useCurrent();


            /*
            |--------------------------------------------------------------------------
            | SOFT DELETE
            |--------------------------------------------------------------------------
            */

            $table->timestampTz('deleted_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEY : PIÈCE DE SUITE
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_piece_suit')
                ->references('id_piece_suit')
                ->on('pieces_suite')
                ->onDelete('restrict');


            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEY : UTILISATEUR
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_utilisateur_creation')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->onDelete('restrict');


            /*
            |--------------------------------------------------------------------------
            | INDEX : DATE
            |--------------------------------------------------------------------------
            */

            $table->index(
                'date_enreg',
                'idx_ca_date_enreg'
            );


            /*
            |--------------------------------------------------------------------------
            | INDEX : SOFT DELETE
            |--------------------------------------------------------------------------
            */

            $table->index(
                'deleted_at',
                'idx_ca_deleted'
            );


            /*
            |--------------------------------------------------------------------------
            | INDEX : PRIORITÉ
            |--------------------------------------------------------------------------
            */

            $table->index(
                'priorite',
                'idx_ca_priorite'
            );


            /*
            |--------------------------------------------------------------------------
            | INDEX : STATUT DOSSIER
            |--------------------------------------------------------------------------
            */

            $table->index(
                'statut_dossier',
                'idx_ca_statut_dossier'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | CONTRAINTE SUR LA PRIORITÉ
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE courriers_arrives
            ADD CONSTRAINT chk_ca_priorite
            CHECK (
                priorite IN (
                    'NORMAL',
                    'URGENT',
                    'TRES_URGENT'
                )
            )
        ");


        /*
        |--------------------------------------------------------------------------
        | CONTRAINTE SUR LE STATUT DU DOSSIER
        |--------------------------------------------------------------------------
        |
        | Valeurs autorisées :
        |
        | En cours
        | Lecture
        | Archivé
        |
        */

        DB::statement("
            ALTER TABLE courriers_arrives
            ADD CONSTRAINT chk_ca_statut_dossier
            CHECK (
                statut_dossier IN (
                    'En cours',
                    'Lecture',
                    'Archivé'
                )
            )
        ");
    }


    /**
     * Supprimer la table courriers_arrives.
     */
    public function down(): void
    {
        Schema::dropIfExists('courriers_arrives');
    }
};