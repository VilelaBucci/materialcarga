<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Criar tabela unidades
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->timestamps();
        });

        // 2. Adicionar unidade_id em setores
        Schema::table('setores', function (Blueprint $table) {
            $table->unsignedBigInteger('unidade_id')->nullable()->after('id');
            $table->foreign('unidade_id')->references('id')->on('unidades')->nullOnDelete();
        });

        // 3. Migrar setores existentes: cada um vira a sua própria unidade
        $now = now();
        foreach (DB::table('setores')->get() as $setor) {
            $unidadeId = DB::table('unidades')->insertGetId([
                'nome'       => $setor->nome,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('setores')->where('id', $setor->id)->update(['unidade_id' => $unidadeId]);
        }
    }

    public function down(): void
    {
        Schema::table('setores', function (Blueprint $table) {
            $table->dropForeign(['unidade_id']);
            $table->dropColumn('unidade_id');
        });
        Schema::dropIfExists('unidades');
    }
};
