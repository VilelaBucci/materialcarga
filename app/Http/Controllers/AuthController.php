<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login()
    {
        $unidades = Unidade::orderBy('nome')->get();
        return view('auth.login', compact('unidades'));
    }

    public function autenticar(Request $request)
    {
        $request->validate([
            'unidade_id' => 'required|exists:unidades,id',
            'setor_id'   => 'required|exists:setores,id',
            'senha'      => 'nullable|string',
        ], [
            'unidade_id.required' => 'Selecione a unidade.',
            'setor_id.required'   => 'Selecione o setor.',
            'setor_id.exists'     => 'Setor inválido.',
        ]);

        $setor   = Setor::find($request->setor_id);
        $unidade = Unidade::find($request->unidade_id);

        // Garante que o setor pertence à unidade selecionada
        if ((int)$setor->unidade_id !== (int)$request->unidade_id) {
            return back()->withErrors(['setor_id' => 'Setor não pertence à unidade selecionada.'])->withInput();
        }

        $isAdmin    = false;
        $isMaster   = false;
        $podeEditar = false;

        // Sem senha, entra em modo somente leitura
        if ($request->filled('senha')) {
            $senhaCorreta = $setor->senha && $request->senha === $setor->senha;

            // Senha admin da unidade — dá acesso admin a qualquer setor dessa unidade
            if ($unidade->senha_adm && $request->senha === $unidade->senha_adm) {
                $senhaCorreta = true;
                $isAdmin      = true;
            }

            // Senha master — acesso total a qualquer unidade/setor
            $senhaMaster = DB::table('configuracoes')->where('chave', 'senha_master')->value('valor');
            if ($senhaMaster && $request->senha === $senhaMaster) {
                $senhaCorreta = true;
                $isAdmin      = true;
                $isMaster     = true;
            }

            if (!$senhaCorreta) {
                return back()->withErrors(['senha' => 'Senha incorreta.'])->withInput();
            }

            $podeEditar = true;
        }

        session([
            'unidade_id'  => $unidade->id,
            'unidade_nome'=> $unidade->nome,
            'setor_id'    => $setor->id,
            'setor_nome'  => $setor->nome,
            'setor_sigla' => $setor->sigla,
            'is_admin'    => $isAdmin,
            'is_master'   => $isMaster,
            'pode_editar' => $podeEditar,
            'ver_todos'   => false,
        ]);

        return redirect()->route('dashboard');
    }

    public function trocarUnidade(Request $request)
    {
        $request->validate([
            'setor_id' => 'required|exists:setores,id',
            'senha'    => 'required|string',
        ], [
            'setor_id.required' => 'Selecione uma unidade/setor.',
            'senha.required'    => 'Informe a senha.',
        ]);

        $setor   = Setor::with('unidade')->find($request->setor_id);
        $unidade = Unidade::find($setor->unidade_id);

        $senhaCorreta = $setor->senha && $request->senha === $setor->senha;
        $isAdmin      = false;
        $isMaster     = false;

        if ($unidade && $unidade->senha_adm && $request->senha === $unidade->senha_adm) {
            $senhaCorreta = true;
            $isAdmin      = true;
        }

        $senhaMaster = DB::table('configuracoes')->where('chave', 'senha_master')->value('valor');
        if ($senhaMaster && $request->senha === $senhaMaster) {
            $senhaCorreta = true;
            $isAdmin      = true;
            $isMaster     = true;
        }

        if (!$senhaCorreta) {
            return redirect()->route('dashboard')
                ->withErrors(['trocar_senha' => 'Senha incorreta.'])
                ->with('abrir_modal_trocar', true);
        }

        session([
            'unidade_id'  => $setor->unidade_id,
            'unidade_nome'=> $setor->unidade?->nome,
            'setor_id'    => $setor->id,
            'setor_nome'  => $setor->nome,
            'setor_sigla' => $setor->sigla,
            'is_admin'    => $isAdmin,
            'is_master'   => $isMaster,
            'pode_editar' => true,
            'ver_todos'   => false,
        ]);

        return redirect()->route('dashboard')
            ->with('sucesso', "Unidade/setor alterado para {$setor->nome}.");
    }

    public function toggleGlobal()
    {
        if (!session('is_admin')) {
            abort(403);
        }
        session(['ver_todos' => !session('ver_todos', false)]);
        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
