<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_numeriques', function (Blueprint $table) {
            $table->increments('num_doc');

            $table->string('nom_original', 50);

            $table->string('nom_stockage', 255)->unique();

            $table->string('extension', 10);

            $table->string('type_mime', 100);

            $table->bigInteger('taille');

            $table->string('chemin', 500);

            $table->char('checksum_sha256', 64);

            $table->unsignedInteger('id_utilisateur_upload');

            $table->timestampTz('created_at')->useCurrent();

            $table->timestampTz('updated_at')->useCurrent();

            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('id_utilisateur_upload')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->onDelete('restrict');

            $table->index(
                'checksum_sha256',
                'idx_docs_checksum'
            );
        });

        DB::statement("
            ALTER TABLE documents_numeriques
            ADD CONSTRAINT chk_documents_taille
            CHECK (taille <= 10485760)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_numeriques');
    }
};