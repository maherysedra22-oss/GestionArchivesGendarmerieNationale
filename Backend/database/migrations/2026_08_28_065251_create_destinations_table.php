<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {

            $table->increments('id_desti');

            $table->string(
                'lib_officiel_desti',
                60
            )->unique();

            $table->boolean('actif')
                ->default(true);

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->timestampTz('updated_at')
                ->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};