<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Adiciona unidade_id em materiais ────────────────────────────────
        Schema::table('materiais', function (Blueprint $table) {
            $table->unsignedBigInteger('unidade_id')->nullable()->after('id');
            $table->foreign('unidade_id')->references('id')->on('unidades')->nullOnDelete();
        });

        // ── 2. Popula unidade_id nos materiais existentes via dependencia ───────
        // Cada material tem dependencia = setor.nome; o setor sabe sua unidade_id
        DB::statement('
            UPDATE materiais m
            JOIN setores s ON s.nome = m.dependencia
            SET m.unidade_id = s.unidade_id
        ');

        // ── 3. Adiciona unidade_id em materiais_backup ─────────────────────────
        Schema::table('materiais_backup', function (Blueprint $table) {
            $table->unsignedBigInteger('unidade_id')->nullable()->after('material_id');
        });

        // ── 4. Troca o unique de setores: de (nome) para (nome, unidade_id) ────
        Schema::table('setores', function (Blueprint $table) {
            $table->dropUnique('setores_nome_unique');
            $table->unique(['nome', 'unidade_id'], 'setores_nome_unidade_unique');
        });
    }

    public function down(): void
    {
        Schema::table('setores', function (Blueprint $table) {
            $table->dropUnique('setores_nome_unidade_unique');
            $table->unique('nome', 'setores_nome_unique');
        });

        Schema::table('materiais_backup', function (Blueprint $table) {
            $table->dropColumn('unidade_id');
        });

        Schema::table('materiais', function (Blueprint $table) {
            $table->dropForeign(['unidade_id']);
            $table->dropColumn('unidade_id');
        });
    }
};
