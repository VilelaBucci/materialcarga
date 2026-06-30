<?php

namespace App\Http\Controllers;

use App\Models\Responsavel;
use App\Models\Setor;
use Illuminate\Http\Request;

class ResponsavelController extends Controller
{
    public function index()
    {
        $setor    = session('setor_nome');
        $isAdmin  = session('is_admin', false);
        $verTodos = $isAdmin && session('ver_todos', false);

        $responsaveis = Responsavel::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->withCount('materiais')
            ->orderBy('nome')->get();

        return view('responsavel.index', compact('responsaveis', 'isAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'         => 'required|string|max:200',
            'graduacao'    => 'nullable|string|max:10',
            'especialidade'=> 'nullable|string|max:10',
        ]);

        $resp = Responsavel::create([
            'nome'          => $request->nome,
            'graduacao'     => $request->graduacao,
            'especialidade' => $request->especialidade,
            'setor'         => session('setor_nome'),
        ]);

        if ($request->expectsJson()) {
            $label = trim(($resp->graduacao ? $resp->graduacao . ' ' : '') . $resp->nome);
            return response()->json(['id' => $resp->id, 'nome' => $label]);
        }
        return redirect()->route('responsaveis.index')->with('sucesso', 'Responsável adicionado.');
    }

    public function update(Request $request, Responsavel $responsavel)
    {
        $request->validate([
            'nome'         => 'required|string|max:200',
            'graduacao'    => 'nullable|string|max:10',
            'especialidade'=> 'nullable|string|max:10',
        ]);

        $responsavel->update($request->only(['nome', 'graduacao', 'especialidade']));
        return redirect()->route('responsaveis.index')->with('sucesso', 'Responsável atualizado.');
    }

    public function destroy(Responsavel $responsavel)
    {
        if ($responsavel->materiais()->count() > 0) {
            return redirect()->route('responsaveis.index')->withErrors(['erro' => 'Este responsável possui materiais vinculados.']);
        }
        $responsavel->delete();
        return redirect()->route('responsaveis.index')->with('sucesso', 'Responsável removido.');
    }
}
