<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $setor     = session('setor_nome');
        $unidadeId = session('unidade_id');
        $isAdmin   = session('is_admin', false);

        $verTodos = $isAdmin && session('ver_todos', false);

        // Sempre filtra pela unidade do usuário; em ver_todos remove o filtro de dependencia
        $query = Material::query()->where('unidade_id', $unidadeId);
        if (!$verTodos) {
            $query->where('dependencia', $setor);
        }

        $totais = [
            'total'      => (clone $query)->count(),
            'ativos'     => (clone $query)->where('situacao', 'A')->count(),
            'em_reparo'  => (clone $query)->where('situacao', 'R')->count(),
            'paralisados'=> (clone $query)->where('situacao', 'P')->count(),
            'sem_local'  => (clone $query)->whereNull('local_id')->count(),
            'sem_funcionar' => (clone $query)->where('funcionando', 'NÃO')->count(),
        ];

        $por_local = (clone $query)
            ->selectRaw('local_id, COUNT(*) as qtd')
            ->with('local')
            ->where(function($q) use ($setor, $verTodos) {
                $q->whereNull('local_id')
                  ->orWhereHas('local', fn($s) => $s->when(!$verTodos, fn($s2) => $s2->where('setor', $setor)));
            })
            ->groupBy('local_id')
            ->orderByDesc('qtd')
            ->limit(10)
            ->get();

        return view('dashboard', compact('totais', 'por_local', 'isAdmin'));
    }
}
