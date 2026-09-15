<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers_depart', function (Blueprint $table) {

            // =====================================================
            // IDENTIFIANT
            // =====================================================

            $table->unsignedInteger('num_ordre_dep')->primary();


            // =====================================================
            // INFORMATIONS DU COURRIER
            // =====================================================

            // Date du courrier
            $table->date('date_dep')->useCurrent();

            // Nature du courrier
            $table->unsignedInteger('num_nat');

            // Objet du courrier
            $table->text('objet_courr_dep');

            // Classement
            $table->unsignedInteger('id_class');

            // Utilisateur qui crée le courrier
            $table->unsignedInteger('id_utilisateur_creation');


            // =====================================================
            // DATES SYSTEME
            // =====================================================

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->timestampTz('updated_at')
                ->useCurrent();

            $table->timestampTz('deleted_at')
                ->nullable();


            // =====================================================
            // FOREIGN KEYS
            // =====================================================

            // Nature du courrier
            $table->foreign('num_nat')
                ->references('num_nat')
                ->on('natures_courriers_depart')
                ->onDelete('restrict');

            // Classement
            $table->foreign('id_class')
                ->references('id_class')
                ->on('classements')
                ->onDelete('restrict');

            // Utilisateur créateur
            $table->foreign('id_utilisateur_creation')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->onDelete('restrict');


            // =====================================================
            // INDEX
            // =====================================================

            $table->index(
                'date_dep',
                'idx_cd_date_dep'
            );

            $table->index(
                'deleted_at',
                'idx_cd_deleted'
            );

            $table->index(
                'num_nat',
                'idx_cd_num_nat'
            );

            $table->index(
                'id_class',
                'idx_cd_id_class'
            );

            $table->index(
                'id_utilisateur_creation',
                'idx_cd_utilisateur_creation'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('courriers_depart');
    }
};