<?php

namespace App\Http\Controllers;

use App\Models\Selecao;
use Illuminate\Http\Request;

class SelecaoController extends Controller
{
    public function index()
    {
        if (!session('pode_editar')) abort(403);
        if (!session('setor_id')) return $this->semSetor();
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
        if (!session('setor_id')) return $this->semSetor();
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

    // Admin que entrou direto em todos os setores não tem um setor dono dos grupos
    private function semSetor()
    {
        return redirect()->route('dashboard')
            ->with('erro', 'Os grupos são de cada setor. Para criar ou gerenciar grupos, entre em um setor.');
    }
}
