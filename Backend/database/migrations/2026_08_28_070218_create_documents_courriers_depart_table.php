<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_courriers_depart', function (Blueprint $table) {
            $table->unsignedInteger('num_ordre_dep');

            $table->unsignedInteger('num_doc');

            $table->primary(
                ['num_ordre_dep', 'num_doc']
            );

            $table->foreign('num_ordre_dep')
                ->references('num_ordre_dep')
                ->on('courriers_depart')
                ->onDelete('cascade');

            $table->foreign('num_doc')
                ->references('num_doc')
                ->on('documents_numeriques')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_courriers_depart');
    }
};