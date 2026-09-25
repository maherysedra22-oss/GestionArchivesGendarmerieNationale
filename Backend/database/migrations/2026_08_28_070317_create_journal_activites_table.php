<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_activites', function (Blueprint $table) {
            $table->bigIncrements('id_journal');

            $table->unsignedInteger('id_utilisateur')->nullable();

            $table->string('nom_utilisateur', 70)->nullable();

            $table->string('action', 30);

            $table->string('table_concernee', 50)->nullable();

            $table->bigInteger('id_enregistrement')->nullable();

            $table->string('reference_objet', 20)->nullable();

            $table->jsonb('donnees_avant')->nullable();

            $table->jsonb('donnees_apres')->nullable();

            $table->string('adresse_ip', 15)->nullable();

            $table->text('user_agent')->nullable();

            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')
                ->on('utilisateurs')
                ->nullOnDelete();

            $table->index('action', 'idx_audit_action');

            $table->index('created_at', 'idx_audit_date');

            $table->index('id_utilisateur', 'idx_audit_user');
        });

        /*
         * Fonction PostgreSQL pour updated_at
         */
        DB::unprepared("
            CREATE OR REPLACE FUNCTION trigger_set_timestamp()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        /*
         * Triggers
         */
        DB::unprepared("
            CREATE TRIGGER set_timestamp_utilisateurs
            BEFORE UPDATE ON utilisateurs
            FOR EACH ROW
            EXECUTE FUNCTION trigger_set_timestamp();
        ");

        DB::unprepared("
            CREATE TRIGGER set_timestamp_ca
            BEFORE UPDATE ON courriers_arrives
            FOR EACH ROW
            EXECUTE FUNCTION trigger_set_timestamp();
        ");

        DB::unprepared("
            CREATE TRIGGER set_timestamp_cd
            BEFORE UPDATE ON courriers_depart
            FOR EACH ROW
            EXECUTE FUNCTION trigger_set_timestamp();
        ");

        DB::unprepared("
            CREATE TRIGGER set_timestamp_docs
            BEFORE UPDATE ON documents_numeriques
            FOR EACH ROW
            EXECUTE FUNCTION trigger_set_timestamp();
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS set_timestamp_utilisateurs
            ON utilisateurs;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS set_timestamp_ca
            ON courriers_arrives;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS set_timestamp_cd
            ON courriers_depart;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS set_timestamp_docs
            ON documents_numeriques;
        ");

        DB::unprepared("
            DROP FUNCTION IF EXISTS trigger_set_timestamp();
        ");

        Schema::dropIfExists('journal_activites');
    }
};