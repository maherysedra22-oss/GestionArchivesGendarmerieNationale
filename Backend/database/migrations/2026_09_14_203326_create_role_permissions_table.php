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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedInteger('id_role');

            $table->unsignedInteger('id_permission');

            $table->foreign('id_role')
                ->references('id_role')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_permission')
                ->references('id_permission')
                ->on('permissions')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique(
                ['id_role', 'id_permission'],
                'uq_role_permissions'
            );

            $table->index(
                'id_role',
                'idx_role_permissions_role'
            );

            $table->index(
                'id_permission',
                'idx_role_permissions_permission'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};