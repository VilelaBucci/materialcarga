<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;

class LocalController extends Controller
{
    public function index()
    {
        $setor    = session('setor_nome');
        $isAdmin  = session('is_admin', false);
        $verTodos = $isAdmin && session('ver_todos', false);

        $locais = Local::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->withCount('materiais')
            ->orderBy('setor')->orderBy('nome')
            ->get();

        return view('local.index', compact('locais', 'isAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:200']);

        $local = Local::create([
            'nome'  => $request->nome,
            'setor' => session('setor_nome'),
        ]);

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
        if ($local->materiais()->count() > 0) {
            return redirect()->route('locais.index')->withErrors(['erro' => 'Este local possui materiais vinculados.']);
        }
        $local->delete();
        return redirect()->route('locais.index')->with('sucesso', 'Local removido.');
    }
}
