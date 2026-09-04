<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_courriers_arrives', function (Blueprint $table) {
            $table->unsignedInteger('num_enreg_courr_arr');

            $table->unsignedInteger('num_doc');

            $table->primary(
                ['num_enreg_courr_arr', 'num_doc']
            );

            $table->foreign('num_enreg_courr_arr')
                ->references('num_enreg_courr_arr')
                ->on('courriers_arrives')
                ->onDelete('cascade');

            $table->foreign('num_doc')
                ->references('num_doc')
                ->on('documents_numeriques')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_courriers_arrives');
    }
};