<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('natures_courriers_depart', function (Blueprint $table) {
            $table->increments('num_nat');
            $table->string('nom_nature', 35)->unique();
            $table->boolean('actif')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('natures_courriers_depart');
    }
};