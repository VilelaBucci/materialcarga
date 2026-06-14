<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Soltar FK temporariamente para poder deletar as unidades erradas
        DB::table('setores')->update(['unidade_id' => null]);
        DB::table('unidades')->delete();

        // 2. Criar UMA única unidade representando a EEAR
        $eearId = DB::table('unidades')->insertGetId([
            'nome'       => 'EEAR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Apontar todos os setores para a unidade EEAR
        DB::table('setores')->update(['unidade_id' => $eearId]);
    }

    public function down(): void
    {
        // Recriar as unidades individuais com os nomes dos setores (rollback)
        $setores = DB::table('setores')->get(['id', 'nome']);

        DB::table('setores')->update(['unidade_id' => null]);
        DB::table('unidades')->delete();

        $now = now();
        foreach ($setores as $setor) {
            $uid = DB::table('unidades')->insertGetId([
                'nome'       => $setor->nome,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('setores')->where('id', $setor->id)->update(['unidade_id' => $uid]);
        }
    }
};
