<?php

namespace App\Http\Controllers;

use App\Models\Selecao;
use Illuminate\Http\Request;

class SelecaoController extends Controller
{
    public function index()
    {
        if (!session('pode_editar')) abort(403);
        $setorId  = session('setor_id');
        $selecoes = Selecao::where('setor_id', $setorId)
            ->withCount('materiais')
            ->orderBy('nome')
            ->get();
        return view('selecao.index', compact('selecoes'));
    }

    public function store(Request $request)
    {
        if (!session('pode_editar')) abort(403);
        $request->validate(['nome' => 'required|string|max:100']);

        $selecao = Selecao::create([
            'nome'     => $request->nome,
            'setor_id' => session('setor_id'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $selecao->id, 'nome' => $selecao->nome]);
        }
        return back()->with('sucesso', "Grupo \"{$selecao->nome}\" criado.");
    }

    public function update(Request $request, Selecao $selecao)
    {
        if (!session('pode_editar')) abort(403);
        if ($selecao->setor_id != session('setor_id') && !session('is_master')) abort(403);

        $request->validate(['nome' => 'required|string|max:100']);
        $selecao->update(['nome' => $request->nome]);
        return back()->with('sucesso', 'Grupo renomeado.');
    }

    public function destroy(Selecao $selecao)
    {
        if (!session('pode_editar')) abort(403);
        if ($selecao->setor_id != session('setor_id') && !session('is_master')) abort(403);

        $nome = $selecao->nome;
        $selecao->delete();
        return back()->with('sucesso', "Grupo \"{$nome}\" removido.");
    }
}
