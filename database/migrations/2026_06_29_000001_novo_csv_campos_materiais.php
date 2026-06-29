<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Amplia situacao para comportar valores textuais do novo CSV SILOMS
        // ("Em Uso", "Estoque Interno", "Em Trânsito", etc. ultrapassam VARCHAR(10))
        DB::statement('ALTER TABLE materiais MODIFY situacao VARCHAR(50) NULL');
        DB::statement('ALTER TABLE materiais_backup MODIFY situacao VARCHAR(50) NULL');

        Schema::table('materiais', function (Blueprint $table) {
            if (!Schema::hasColumn('materiais', 'unidade_implantou')) {
                $table->string('unidade_implantou', 200)->nullable()->after('dependencia');
            }
            if (!Schema::hasColumn('materiais', 'sigilo')) {
                $table->string('sigilo', 20)->nullable()->after('situacao');
            }
            if (!Schema::hasColumn('materiais', 'data_implantacao')) {
                $table->date('data_implantacao')->nullable()->after('sigilo');
            }
        });

        Schema::table('materiais_backup', function (Blueprint $table) {
            if (!Schema::hasColumn('materiais_backup', 'unidade_implantou')) {
                $table->string('unidade_implantou', 200)->nullable()->after('dependencia');
            }
            if (!Schema::hasColumn('materiais_backup', 'sigilo')) {
                $table->string('sigilo', 20)->nullable()->after('situacao');
            }
            if (!Schema::hasColumn('materiais_backup', 'data_implantacao')) {
                $table->date('data_implantacao')->nullable()->after('sigilo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materiais', function (Blueprint $table) {
            $table->dropColumn(['unidade_implantou', 'sigilo', 'data_implantacao']);
        });
        Schema::table('materiais_backup', function (Blueprint $table) {
            $table->dropColumn(['unidade_implantou', 'sigilo', 'data_implantacao']);
        });
        DB::statement('ALTER TABLE materiais MODIFY situacao VARCHAR(10) NULL');
        DB::statement('ALTER TABLE materiais_backup MODIFY situacao VARCHAR(10) NULL');
    }
};
