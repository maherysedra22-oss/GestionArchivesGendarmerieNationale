<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers_depart', function (Blueprint $table) {
            $table->increments('num_ordre_dep');

            $table->string('reference', 30)->unique();

            $table->date('date_dep')->useCurrent();

            $table->unsignedInteger('num_nat');

            $table->text('objet_courr_dep');

            $table->unsignedInteger('id_class');

            $table->unsignedInteger('id_utilisateur_creation');

            $table->string('priorite', 20)->default('NORMAL');

            $table->text('observations')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            $table->timestampTz('updated_at')->useCurrent();

            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('num_nat')
                ->references('num_nat')
                ->on('natures_courriers_depart')
                ->onDelete('restrict');

            $table->foreign('id_class')
                ->references('id_class')
                ->on('classements')
                ->onDelete('restrict');

            $table->foreign('id_utilisateur_creation')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->onDelete('restrict');

            $table->index('reference', 'idx_cd_reference');
            $table->index('date_dep', 'idx_cd_date_dep');
            $table->index('deleted_at', 'idx_cd_deleted');
            $table->index('priorite', 'idx_cd_priorite');
        });

        DB::statement("
            ALTER TABLE courriers_depart
            ADD CONSTRAINT chk_cd_priorite
            CHECK (priorite IN ('NORMAL', 'URGENT', 'TRES_URGENT'))
        ");

        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS seq_courrier_depart_annee
            START WITH 1
        ");
    }

    public function down(): void
    {
        DB::statement(
            'DROP SEQUENCE IF EXISTS seq_courrier_depart_annee'
        );

        Schema::dropIfExists('courriers_depart');
    }
};