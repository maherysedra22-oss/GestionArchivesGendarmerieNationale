<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destiner', function (Blueprint $table) {

            // Courrier départ
            $table->unsignedInteger('num_ordre_dep');

            // Destination
            $table->unsignedInteger('id_desti');


            // Dates
            $table->timestampTz('created_at')
                ->useCurrent();

            $table->timestampTz('updated_at')
                ->useCurrent();


            // Clé primaire composée
            $table->primary([
                'num_ordre_dep',
                'id_desti'
            ]);


            // Foreign Key Courrier Départ
            $table->foreign('num_ordre_dep')
                ->references('num_ordre_dep')
                ->on('courriers_depart')
                ->onDelete('cascade');


            // Foreign Key Destination
            $table->foreign('id_desti')
                ->references('id_desti')
                ->on('destinations')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destiner');
    }
};