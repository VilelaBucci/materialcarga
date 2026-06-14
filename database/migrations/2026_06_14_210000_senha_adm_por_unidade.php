<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona senha_adm à tabela de unidades (idempotente)
        if (!Schema::hasColumn('unidades', 'senha_adm')) {
            Schema::table('unidades', function (Blueprint $table) {
                $table->string('senha_adm')->nullable()->after('nome');
            });
        }

        // Migra senha_adm dos setores para a unidade correspondente
        // (toma o primeiro valor não-nulo por unidade)
        $rows = DB::table('setores')
            ->whereNotNull('senha_adm')
            ->select('unidade_id', 'senha_adm')
            ->orderBy('id')
            ->get();

        $vistas = [];
        foreach ($rows as $row) {
            if (!isset($vistas[$row->unidade_id])) {
                DB::table('unidades')
                    ->where('id', $row->unidade_id)
                    ->update(['senha_adm' => $row->senha_adm]);
                $vistas[$row->unidade_id] = true;
            }
        }

        // Torna senha nullable para poder limpar os defaults
        Schema::table('setores', function (Blueprint $table) {
            $table->string('senha')->nullable()->change();
        });

        // Zera as senhas default "12345" dos setores
        DB::table('setores')->where('senha', '12345')->update(['senha' => null]);

        // Remove senha_adm dos setores (movida para unidades)
        DB::table('setores')->update(['senha_adm' => null]);
    }

    public function down(): void
    {
        // Restaura senha_adm nos setores a partir das unidades
        $unidades = DB::table('unidades')->whereNotNull('senha_adm')->get();
        foreach ($unidades as $u) {
            DB::table('setores')
                ->where('unidade_id', $u->id)
                ->whereNull('senha_adm')
                ->update(['senha_adm' => $u->senha_adm]);
        }

        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn('senha_adm');
        });
    }
};
