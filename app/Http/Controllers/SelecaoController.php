<?php

namespace App\Http\Controllers;

use App\Models\Selecao;
use App\Models\Setor;
use Illuminate\Http\Request;

class SelecaoController extends Controller
{
    public function index()
    {
        if (!session('pode_editar')) abort(403);
        $unidadeId = session('unidade_id');
        $verTodos  = session('is_admin') && session('ver_todos');

        // Grupos são de cada setor; no modo todos os setores, os de todos os setores da unidade
        $selecoes = Selecao::with('setor')
            ->when($verTodos,
                fn($q) => $q->whereHas('setor', fn($s) => $s->where('unidade_id', $unidadeId)),
                fn($q) => $q->where('setor_id', session('setor_id')))
            ->withCount('materiais')
            ->orderBy('nome')
            ->get();

        // No modo todos os setores, o grupo novo precisa de um setor escolhido
        $setores = $verTodos ? Setor::where('unidade_id', $unidadeId)->orderBy('nome')->get() : collect();

        return view('selecao.index', compact('selecoes', 'verTodos', 'setores'));
    }

    public function store(Request $request)
    {
        if (!session('pode_editar')) abort(403);
        $request->validate(['nome' => 'required|string|max:100']);

        $setor = $this->setorParaVincular($request);
        if (!$setor) {
            return $request->expectsJson()
                ? response()->json(['erro' => 'Escolha o setor do grupo.'], 422)
                : back()->withErrors(['setor_id' => 'Escolha o setor do grupo.'])->withInput();
        }

        $selecao = Selecao::create([
            'nome'     => $request->nome,
            'setor_id' => $setor->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $selecao->id, 'nome' => $selecao->nome]);
        }
        return back()->with('sucesso', "Grupo \"{$selecao->nome}\" criado.");
    }

    public function update(Request $request, Selecao $selecao)
    {
        if (!session('pode_editar')) abort(403);
        $this->autorizar($selecao);

        $request->validate(['nome' => 'required|string|max:100']);
        $selecao->update(['nome' => $request->nome]);
        return back()->with('sucesso', 'Grupo renomeado.');
    }

    public function destroy(Selecao $selecao)
    {
        if (!session('pode_editar')) abort(403);
        $this->autorizar($selecao);

        $nome = $selecao->nome;
        $selecao->delete();
        return back()->with('sucesso', "Grupo \"{$nome}\" removido.");
    }

    // Grupo do próprio setor; no modo todos os setores, de qualquer setor da unidade; master, qualquer um
    private function autorizar(Selecao $selecao): void
    {
        $daUnidade = session('is_admin') && session('ver_todos')
            && (int)$selecao->setor?->unidade_id === (int)session('unidade_id');

        if ($selecao->setor_id != session('setor_id') && !$daUnidade && !session('is_master')) abort(403);
    }
}
