<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Um local ou responsável pode atender vários setores. Antes era um só, gravado pelo nome na coluna `setor`
        // (que continua existindo como setor de cadastro).
        Schema::create('local_setor', function (Blueprint $table) {
            $table->foreignId('local_id')->constrained('locais')->cascadeOnDelete();
            $table->foreignId('setor_id')->constrained('setores')->cascadeOnDelete();
            $table->primary(['local_id', 'setor_id']);
        });

        Schema::create('responsavel_setor', function (Blueprint $table) {
            $table->foreignId('responsavel_id')->constrained('responsaveis')->cascadeOnDelete();
            $table->foreignId('setor_id')->constrained('setores')->cascadeOnDelete();
            $table->primary(['responsavel_id', 'setor_id']);
        });

        foreach (['local_setor' => ['locais', 'local_id'], 'responsavel_setor' => ['responsaveis', 'responsavel_id']] as $pivot => [$tabela, $coluna]) {
            // ── 1. Setor de cadastro ────────────────────────────────────────────
            // O nome do setor se repete entre unidades. Se o item já é usado por materiais, fica só nas unidades
            // desses materiais; se não é usado, fica em todos os setores com esse nome, como o sistema já mostrava.
            DB::statement("
                INSERT IGNORE INTO {$pivot} ({$coluna}, setor_id)
                SELECT x.id, s.id
                FROM {$tabela} x
                JOIN setores s ON s.nome = x.setor
                WHERE NOT EXISTS (SELECT 1 FROM materiais m WHERE m.{$coluna} = x.id)
                   OR s.unidade_id IN (SELECT m.unidade_id FROM materiais m WHERE m.{$coluna} = x.id)
            ");

            // ── 2. Setores dos materiais que já usam o item ─────────────────────
            DB::statement("
                INSERT IGNORE INTO {$pivot} ({$coluna}, setor_id)
                SELECT DISTINCT m.{$coluna}, s.id
                FROM materiais m
                JOIN setores s ON s.nome = m.dependencia AND s.unidade_id = m.unidade_id
                WHERE m.{$coluna} IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('responsavel_setor');
        Schema::dropIfExists('local_setor');
    }
};
