<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_courriers_depart', function (Blueprint $table) {

            // Courrier départ
            $table->unsignedInteger('num_ordre_dep');

            // Document numérique
            $table->unsignedInteger('num_doc');


            // Clé primaire composée
            $table->primary([
                'num_ordre_dep',
                'num_doc'
            ]);


            // Foreign Key Courrier
            $table->foreign('num_ordre_dep')
                ->references('num_ordre_dep')
                ->on('courriers_depart')
                ->onDelete('cascade');


            // Foreign Key Document
            $table->foreign('num_doc')
                ->references('num_doc')
                ->on('documents_numeriques')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'documents_courriers_depart'
        );
    }
};