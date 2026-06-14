<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reparos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materiais')->cascadeOnDelete();
            $table->text('descricao');
            $table->date('data_inicio');
            $table->date('data_conclusao')->nullable();
            $table->enum('status', ['em_andamento', 'concluido', 'cancelado'])->default('em_andamento');
            $table->string('setor_responsavel', 200)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reparos');
    }
};
