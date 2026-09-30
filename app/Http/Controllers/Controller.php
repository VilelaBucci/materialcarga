<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use Illuminate\Http\Request;

abstract class Controller
{
    // Setor ao qual um local/responsável novo fica vinculado: o setor_id enviado pelo formulário
    // (qualquer setor da unidade no modo todos os setores; senão, só o próprio) ou o setor da sessão
    protected function setorParaVincular(Request $request): ?Setor
    {
        $verTodos = session('is_admin') && session('ver_todos');
        $setorId  = $request->input('setor_id') ?: session('setor_id');

        if (!$setorId || (!$verTodos && (int)$setorId !== (int)session('setor_id'))) {
            return null;
        }

        return Setor::where('id', $setorId)->where('unidade_id', session('unidade_id'))->first();
    }
}
