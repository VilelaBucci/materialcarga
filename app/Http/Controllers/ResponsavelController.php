<?php

namespace App\Http\Controllers;

use App\Models\Responsavel;
use App\Models\Setor;
use Illuminate\Http\Request;

class ResponsavelController extends Controller
{
    public function index()
    {
        $setor     = session('setor_nome');
        $setorId   = session('setor_id');
        $unidadeId = session('unidade_id');
        $isAdmin   = session('is_admin', false);
        $verTodos  = $isAdmin && session('ver_todos', false);

        // No setor, conta só os itens do setor; no modo todos os setores, os da unidade
        $responsaveis = Responsavel::visiveisPara($setorId, $unidadeId, $verTodos)
            ->with(['setores' => fn($q) => $q->where('setores.unidade_id', $unidadeId)->orderBy('nome')])
            ->withCount(['materiais' => fn($q) => $q->where('unidade_id', $unidadeId)
                ->when(!$verTodos, fn($q2) => $q2->where('dependencia', $setor))])
            ->orderBy('nome')->get();

        // No modo todos os setores, o responsável novo precisa de um setor escolhido
        $setores = $verTodos ? Setor::where('unidade_id', $unidadeId)->orderBy('nome')->get() : collect();

        return view('responsavel.index', compact('responsaveis', 'isAdmin', 'verTodos', 'setores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'         => 'required|string|max:200',
            'graduacao'    => 'nullable|string|max:10',
            'especialidade'=> 'nullable|string|max:10',
        ]);

        $setor = $this->setorParaVincular($request);
        if (!$setor) {
            return $request->expectsJson()
                ? response()->json(['erro' => 'Escolha o setor do responsável.'], 422)
                : back()->withErrors(['setor_id' => 'Escolha o setor do responsável.'])->withInput();
        }

        $resp = Responsavel::create([
            'nome'          => $request->nome,
            'graduacao'     => $request->graduacao,
            'especialidade' => $request->especialidade,
            'setor'         => $setor->nome,
        ]);
        $resp->setores()->attach($setor->id);

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
        $verTodos = session('is_admin') && session('ver_todos');

        // No setor, se o responsável também atende outros setores, remover só tira o vínculo com este setor
        if (!$verTodos && $responsavel->setores()->where('setores.id', '<>', session('setor_id'))->exists()) {
            $usadoAqui = $responsavel->materiais()
                ->where('unidade_id', session('unidade_id'))
                ->where('dependencia', session('setor_nome'))
                ->exists();
            if ($usadoAqui) {
                return redirect()->route('responsaveis.index')->withErrors(['erro' => 'Este responsável possui materiais vinculados neste setor.']);
            }
            $responsavel->setores()->detach(session('setor_id'));
            return redirect()->route('responsaveis.index')->with('sucesso', 'Responsável removido deste setor.');
        }

        if ($responsavel->materiais()->count() > 0) {
            return redirect()->route('responsaveis.index')->withErrors(['erro' => 'Este responsável possui materiais vinculados.']);
        }
        $responsavel->delete();
        return redirect()->route('responsaveis.index')->with('sucesso', 'Responsável removido.');
    }
}
