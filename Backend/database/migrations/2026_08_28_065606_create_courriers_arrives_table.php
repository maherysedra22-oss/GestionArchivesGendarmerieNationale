<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers_arrives', function (Blueprint $table) {
            $table->increments('num_enreg_courr_arr');

            $table->string('reference', 30)->unique();

            $table->date('date_enreg')->useCurrent();

            $table->string('num_ordre_orig', 100);

            $table->string('lib_orig', 255);

            $table->text('objet_courr_arri');

            $table->unsignedInteger('id_piece_suit');

            $table->unsignedInteger('id_class');

            $table->unsignedInteger('id_utilisateur_creation');

            $table->string('priorite', 20)->default('NORMAL');

            $table->text('observations')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            $table->timestampTz('updated_at')->useCurrent();

            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('id_piece_suit')
                ->references('id_piece_suit')
                ->on('pieces_suite')
                ->onDelete('restrict');

            $table->foreign('id_class')
                ->references('id_class')
                ->on('classements')
                ->onDelete('restrict');

            $table->foreign('id_utilisateur_creation')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->onDelete('restrict');

            $table->index('reference', 'idx_ca_reference');
            $table->index('date_enreg', 'idx_ca_date_enreg');
            $table->index('deleted_at', 'idx_ca_deleted');
            $table->index('priorite', 'idx_ca_priorite');
        });

        DB::statement("
            ALTER TABLE courriers_arrives
            ADD CONSTRAINT chk_ca_priorite
            CHECK (priorite IN ('NORMAL', 'URGENT', 'TRES_URGENT'))
        ");

        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS seq_courrier_arrive_annee
            START WITH 1
        ");
    }

    public function down(): void
    {
        DB::statement(
            'DROP SEQUENCE IF EXISTS seq_courrier_arrive_annee'
        );

        Schema::dropIfExists('courriers_arrives');
    }
};