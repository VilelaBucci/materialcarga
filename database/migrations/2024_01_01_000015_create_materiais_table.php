<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materiais', function (Blueprint $table) {
            $table->id();
            $table->string('dependencia', 200)->nullable()->index();
            $table->string('conta', 200)->nullable()->index();
            $table->string('classe', 50)->nullable();
            $table->integer('num_bmp')->nullable()->index();
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
            $table->enum('situacao', ['A', 'D', 'P', 'R'])->nullable()->index();
            $table->enum('em_uso', ['SIM', 'NÃO'])->nullable();
            $table->enum('funcionando', ['SIM', 'NÃO'])->nullable();
            $table->text('mais_informacoes')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('responsaveis')->nullOnDelete();
            $table->foreignId('local_id')->nullable()->constrained('locais')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiais');
    }
};
