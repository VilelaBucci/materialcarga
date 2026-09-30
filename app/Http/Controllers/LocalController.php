<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\Setor;
use Illuminate\Http\Request;

class LocalController extends Controller
{
    public function index()
    {
        $setor     = session('setor_nome');
        $setorId   = session('setor_id');
        $unidadeId = session('unidade_id');
        $isAdmin   = session('is_admin', false);
        $verTodos  = $isAdmin && session('ver_todos', false);

        // No setor, conta só os itens do setor; no modo todos os setores, os da unidade
        $locais = Local::visiveisPara($setorId, $unidadeId, $verTodos)
            ->with(['setores' => fn($q) => $q->where('setores.unidade_id', $unidadeId)->orderBy('nome')])
            ->withCount(['materiais' => fn($q) => $q->where('unidade_id', $unidadeId)
                ->when(!$verTodos, fn($q2) => $q2->where('dependencia', $setor))])
            ->orderBy('nome')
            ->get();

        // No modo todos os setores, o local novo precisa de um setor escolhido
        $setores = $verTodos ? Setor::where('unidade_id', $unidadeId)->orderBy('nome')->get() : collect();

        return view('local.index', compact('locais', 'isAdmin', 'verTodos', 'setores'));
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:200']);

        $setor = $this->setorParaVincular($request);
        if (!$setor) {
            return $request->expectsJson()
                ? response()->json(['erro' => 'Escolha o setor do local.'], 422)
                : back()->withErrors(['setor_id' => 'Escolha o setor do local.'])->withInput();
        }

        $local = Local::create([
            'nome'  => $request->nome,
            'setor' => $setor->nome,
        ]);
        $local->setores()->attach($setor->id);

        if ($request->expectsJson()) {
            return response()->json(['id' => $local->id, 'nome' => $local->nome]);
        }
        return redirect()->route('locais.index')->with('sucesso', 'Local criado.');
    }

    public function update(Request $request, Local $local)
    {
        $request->validate(['nome' => 'required|string|max:200']);
        $local->update(['nome' => $request->nome]);
        return redirect()->route('locais.index')->with('sucesso', 'Local atualizado.');
    }

    public function destroy(Local $local)
    {
        $verTodos = session('is_admin') && session('ver_todos');

        // No setor, se o local também atende outros setores, remover só tira o vínculo com este setor
        if (!$verTodos && $local->setores()->where('setores.id', '<>', session('setor_id'))->exists()) {
            $usadoAqui = $local->materiais()
                ->where('unidade_id', session('unidade_id'))
                ->where('dependencia', session('setor_nome'))
                ->exists();
            if ($usadoAqui) {
                return redirect()->route('locais.index')->withErrors(['erro' => 'Este local possui materiais vinculados neste setor.']);
            }
            $local->setores()->detach(session('setor_id'));
            return redirect()->route('locais.index')->with('sucesso', 'Local removido deste setor.');
        }

        if ($local->materiais()->count() > 0) {
            return redirect()->route('locais.index')->withErrors(['erro' => 'Este local possui materiais vinculados.']);
        }
        $local->delete();
        return redirect()->route('locais.index')->with('sucesso', 'Local removido.');
    }
}
