<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->increments('id_utilisateur');

            $table->string('matricule', 30)->unique();

            $table->string('nom', 30);

            $table->string('prenom', 35)->nullable();

            $table->string('poste_fonction', 40);

            $table->string('email', 60)->unique();

            $table->string('mot_de_passe', 255);

            $table->unsignedInteger('id_grade');

            $table->unsignedInteger('id_role');

            $table->boolean('statut')->default(true);

            $table->timestampTz('derniere_connexion')->nullable();

            $table->boolean('doit_changer_mdp')->default(false);

            $table->timestampTz('created_at')->useCurrent();

            $table->timestampTz('updated_at')->useCurrent();

            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('id_grade')
                ->references('id_grade')
                ->on('grades_militaires')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_role')
                ->references('id_role')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('matricule', 'idx_utilisateurs_matricule');
            $table->index('email', 'idx_utilisateurs_email');
            $table->index('id_role', 'idx_utilisateurs_role');
            $table->index('deleted_at', 'idx_utilisateurs_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};