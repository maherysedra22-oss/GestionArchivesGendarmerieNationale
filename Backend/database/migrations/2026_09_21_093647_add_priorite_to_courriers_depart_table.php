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
        Schema::table('courriers_depart', function (Blueprint $table) {
            $table->enum('priorite', ['NORMAL', 'URGENT', 'TRES_URGENT'])->default('NORMAL')->after('id_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courriers_depart', function (Blueprint $table) {
            $table->dropColumn('priorite');
        });
    }
};
