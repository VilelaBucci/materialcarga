<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('selecoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->unsignedBigInteger('setor_id');
            $table->foreign('setor_id')->references('id')->on('setores')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('material_selecao', function (Blueprint $table) {
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('selecao_id');
            $table->primary(['material_id', 'selecao_id']);
            $table->foreign('material_id')->references('id')->on('materiais')->cascadeOnDelete();
            $table->foreign('selecao_id')->references('id')->on('selecoes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_selecao');
        Schema::dropIfExists('selecoes');
    }
};
