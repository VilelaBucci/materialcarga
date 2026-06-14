<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE materiais MODIFY situacao VARCHAR(10) NULL");

        Schema::create('materiais_backup', function (Blueprint $table) {
            $table->id();
            $table->timestamp('backup_at')->useCurrent();
            $table->string('backup_label', 150)->nullable();
            $table->unsignedBigInteger('material_id')->nullable();
            $table->string('dependencia', 200)->nullable();
            $table->string('conta', 200)->nullable();
            $table->string('classe', 50)->nullable();
            $table->integer('num_bmp')->nullable();
            $table->text('nomenclatura')->nullable();
            $table->string('num_serie', 100)->nullable();
            $table->string('fcg', 100)->nullable();
            $table->string('num_pn', 100)->nullable();
            $table->string('num_sispat', 100)->nullable();
            $table->string('etiqueta_metalica', 100)->nullable();
            $table->smallInteger('quantidade')->default(1);
            $table->decimal('valor_atualizado', 15, 2)->default(0);
            $table->decimal('valor_depreciacao', 15, 2)->default(0);
            $table->decimal('valor_liquido', 15, 2)->default(0);
            $table->string('situacao', 10)->nullable();
            $table->string('em_uso', 10)->nullable();
            $table->string('funcionando', 10)->nullable();
            $table->text('mais_informacoes')->nullable();
            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->unsignedBigInteger('local_id')->nullable();
            $table->index('backup_at');
            $table->index('material_id');
            $table->index('num_sispat');
            $table->index('num_bmp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiais_backup');
        DB::statement("ALTER TABLE materiais MODIFY situacao ENUM('A','D','P','R') NULL");
    }
};
