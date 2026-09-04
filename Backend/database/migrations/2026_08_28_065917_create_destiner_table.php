<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destiner', function (Blueprint $table) {
            $table->unsignedInteger('num_ordre_dep');

            $table->unsignedInteger('id_desti');

            $table->timestampTz('created_at')->useCurrent();

            $table->primary(
                ['num_ordre_dep', 'id_desti']
            );

            $table->foreign('num_ordre_dep')
                ->references('num_ordre_dep')
                ->on('courriers_depart')
                ->onDelete('cascade');

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