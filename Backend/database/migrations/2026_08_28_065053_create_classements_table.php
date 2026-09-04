<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classements', function (Blueprint $table) {
            $table->increments('id_class');

            $table->string('nom_class', 150);

            $table->string('type_courrier', 10);

            $table->boolean('actif')->default(true);

            $table->timestampTz('created_at')->useCurrent();

            $table->timestampTz('updated_at')->useCurrent();

            $table->unique(
                ['nom_class', 'type_courrier'],
                'uq_classement_type'
            );
        });

        DB::statement("
            ALTER TABLE classements
            ADD CONSTRAINT chk_classement_type
            CHECK (type_courrier IN ('ARRIVE', 'DEPART'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('classements');
    }
};