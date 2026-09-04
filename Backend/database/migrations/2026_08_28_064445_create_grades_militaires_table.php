<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades_militaires', function (Blueprint $table) {
            $table->increments('id_grade');
            $table->string('nom_grade', 100)->unique();
            $table->integer('ordre_hierarchique')->unique();
            $table->string('categorie', 50);
            $table->string('insigne_symbole', 20)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement("
            ALTER TABLE grades_militaires
            ADD CONSTRAINT chk_grades_ordre
            CHECK (ordre_hierarchique BETWEEN 1 AND 16)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('grades_militaires');
    }
};