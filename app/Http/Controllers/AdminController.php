<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function setores()
    {
        if (!session('is_admin')) abort(403);

        $setores = Setor::where('unidade_id', session('unidade_id'))->orderBy('nome')->get();
        return view('admin.setores', compact('setores'));
    }

    public function atualizarSenha(Request $request, Setor $setor)
    {
        if (!session('is_admin')) abort(403);
        if ((int)$setor->unidade_id !== (int)session('unidade_id')) abort(403);

        $request->validate([
            'senha' => 'required|string|min:4|max:50',
        ], [
            'senha.required' => 'Informe a nova senha.',
            'senha.min'      => 'A senha deve ter pelo menos 4 caracteres.',
        ]);

        $setor->update(['senha' => $request->senha]);

        return redirect()->route('admin.setores')->with('sucesso', "Senha do setor {$setor->nome} atualizada.");
    }

    public function atualizarSenhaAdm(Request $request)
    {
        if (!session('is_admin')) abort(403);

        $isMaster = session('is_master');

        $request->validate([
            'senha_atual' => $isMaster ? 'nullable|string' : 'required|string',
            'senha_nova'  => 'required|string|min:6|max:50',
            'senha_conf'  => 'required|string|same:senha_nova',
        ], [
            'senha_atual.required' => 'Informe a senha atual.',
            'senha_nova.required'  => 'Informe a nova senha.',
            'senha_nova.min'       => 'A nova senha deve ter pelo menos 6 caracteres.',
            'senha_conf.same'      => 'A confirmação não confere com a nova senha.',
        ]);

        $unidade = Unidade::find(session('unidade_id'));
        if (!session('is_master') && (!$unidade || $unidade->senha_adm !== $request->senha_atual)) {
            return back()->withErrors(['senha_atual' => 'Senha atual incorreta.']);
        }

        $unidade->update(['senha_adm' => $request->senha_nova]);

        return redirect()->route('admin.setores')->with('sucesso', 'Senha do administrador da unidade atualizada com sucesso.');
    }

    // ── Senha Master ─────────────────────────────────────────────────────────

    public function masterForm()
    {
        if (!session('is_master')) abort(403);
        return view('admin.master');
    }

    public function masterAtualizar(Request $request)
    {
        if (!session('is_master')) abort(403);

        $request->validate([
            'senha_nova' => 'required|string|min:8|max:100',
            'senha_conf' => 'required|string|same:senha_nova',
        ], [
            'senha_nova.required' => 'Informe a nova senha master.',
            'senha_nova.min'      => 'A senha master deve ter pelo menos 8 caracteres.',
            'senha_conf.same'     => 'A confirmação não confere.',
        ]);

        DB::table('configuracoes')
            ->where('chave', 'senha_master')
            ->update(['valor' => $request->senha_nova, 'updated_at' => now()]);

        return back()->with('sucesso', 'Senha master atualizada com sucesso.');
    }

    // ── Nova Unidade (público, mas exige senha_adm) ───────────────────────────

    public function novaUnidade()
    {
        return view('admin.nova-unidade');
    }

    public function validarCsv(Request $request)
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:30720']);

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
        }

        $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $header = array_shift($linhas);
        $linhas = array_filter($linhas, fn($l) => trim($l) !== '');

        if (!$this->headerValido($header)) {
            return response()->json([
                'valido' => false,
                'erro'   => 'O arquivo não parece ser um CSV do SILOMS. Verifique se o arquivo contém as colunas idPatrimonio e setor (mínimo 14 colunas separadas por vírgula).',
            ]);
        }

        // Coleta todas as dependências únicas (coluna 2: setor)
        $dependencias = [];
        foreach ($linhas as $linha) {
            $c   = str_getcsv(trim($linha));
            $dep = isset($c[2]) ? trim($c[2]) : '';
            if ($dep !== '') $dependencias[$dep] = true;
        }

        return response()->json([
            'valido'       => true,
            'dependencias' => array_keys($dependencias),
            'total_linhas' => count($linhas),
        ]);
    }

    public function criarUnidade(Request $request)
    {
        $request->validate([
            'csv'       => 'required|file|mimes:csv,txt|max:30720',
            'nome'      => 'required|string|max:200|unique:unidades,nome',
            'senha_ini' => 'required|string|min:4|max:50',
            'senha_adm' => 'required|string|min:6|max:50',
        ], [
            'nome.unique'      => 'Já existe uma unidade com este nome. Procure-a no primeiro select da página de login e faça o acesso normalmente.',
            'nome.required'    => 'Informe o nome da unidade.',
            'senha_ini.required' => 'Informe a senha inicial dos setores.',
            'senha_ini.min'    => 'A senha deve ter pelo menos 4 caracteres.',
            'senha_adm.required' => 'Informe a senha de administrador.',
            'senha_adm.min'      => 'A senha de administrador deve ter pelo menos 6 caracteres.',
        ]);

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
        }

        $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $header = array_shift($linhas);
        $linhas = array_filter($linhas, fn($l) => trim($l) !== '');

        if (!$this->headerValido($header)) {
            return back()->withErrors(['csv' => 'O arquivo não é um CSV válido do SILOMS. Verifique se o arquivo contém as colunas idPatrimonio e setor (mínimo 14 colunas separadas por vírgula).'])->withInput();
        }

        if (count($linhas) === 0) {
            return back()->withErrors(['csv' => 'O arquivo está vazio ou sem dados após o cabeçalho.'])->withInput();
        }

        DB::transaction(function () use ($request, $linhas) {
            // 1. Criar a Unidade com senha de administrador
            $unidade = Unidade::create([
                'nome'      => $request->nome,
                'senha_adm' => $request->senha_adm,
            ]);

            $agora      = now();
            $lote       = [];
            $loteSize   = 500;
            $setoresMap = [];

            // Novo formato SILOMS (vírgula, 14 colunas):
            // 0:idPatrimonio  1:unidadeImplantou  2:setor  3:unidadeSetor  4:sigilo
            // 5:situacao  6:cdClasse  7:dsClasse  8:contaContabil  9:subElemento
            // 10:descricao  11:nrPn  12:nrSerie  13:dataImplantacao
            foreach ($linhas as $linha) {
                $c = str_getcsv(trim($linha));
                if (count($c) < 14) continue;

                $dep = $this->ns($c[2]) ?? $request->nome;

                // 2. Cria o Setor para esta dependência se ainda não existe
                if (!isset($setoresMap[$dep])) {
                    $setor = Setor::create([
                        'nome'       => $dep,
                        'sigla'      => mb_strtoupper(mb_substr($dep, 0, 10)),
                        'senha'      => $request->senha_ini,
                        'unidade_id' => $unidade->id,
                    ]);
                    $setoresMap[$dep] = $setor->id;
                }

                // 3. Monta o registro de material
                $lote[] = [
                    'dependencia'       => $dep,
                    'unidade_id'        => $unidade->id,
                    'unidade_implantou' => $this->ns($c[1]),
                    'conta'             => $this->ns($c[8]),
                    'classe'            => $this->ns($c[7]),
                    'num_bmp'           => $this->intVal($c[0]),
                    'nomenclatura'      => $this->ns($c[10]),
                    'num_serie'         => $this->ns($c[12]),
                    'num_pn'            => $this->ns($c[11]),
                    'sigilo'            => $this->ns($c[4]),
                    'situacao'          => $this->ns($c[5]),
                    'data_implantacao'  => $this->dateVal($c[13]),
                    'created_at'        => $agora,
                    'updated_at'        => $agora,
                ];

                if (count($lote) >= $loteSize) {
                    DB::table('materiais')->insert($lote);
                    $lote = [];
                }
            }

            if (!empty($lote)) {
                DB::table('materiais')->insert($lote);
            }
        });

        $unidade = Unidade::where('nome', $request->nome)->first();
        $qtdSetores = $unidade->setores()->count();

        return redirect()->route('login')
            ->with('sucesso', "Unidade \"{$unidade->nome}\" criada com {$qtdSetores} setor(es). Você já pode acessá-la no login.");
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function headerValido(string $header): bool
    {
        $h = mb_strtoupper(trim($header));
        $colunas = str_getcsv($h);
        return str_contains($h, 'IDPATRIMONIO')
            && str_contains($h, 'SETOR')
            && count($colunas) >= 14;
    }

    private function ns(?string $val): ?string
    {
        $v = trim((string)$val);
        return $v === '' ? null : $v;
    }

    private function intVal(?string $val): ?int
    {
        $v = trim((string)$val);
        return is_numeric($v) ? (int)$v : null;
    }

    private function dateVal(?string $val): ?string
    {
        $v = trim((string)$val);
        if ($v === '') return null;
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $v, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
            return substr($v, 0, 10);
        }
        return null;
    }

    private function dec(?string $val): float
    {
        $v = str_replace('.', '', trim((string)$val));
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : 0.0;
    }
}
