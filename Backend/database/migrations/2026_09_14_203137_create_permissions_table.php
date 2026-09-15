<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id_permission');

            $table->string('code_permission', 100)
                ->unique();

            $table->string('nom_permission', 150);

            $table->string('page', 100);

            $table->string('action', 50);

            $table->text('description')
                ->nullable();

            $table->boolean('actif')
                ->default(true);

            $table->timestampTz('created_at')
                ->useCurrent();

            $table->timestampTz('updated_at')
                ->useCurrent();

            $table->index('page', 'idx_permissions_page');

            $table->index('action', 'idx_permissions_action');

            $table->index('actif', 'idx_permissions_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};