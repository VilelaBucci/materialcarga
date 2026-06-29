<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacaoController extends Controller
{
    public function formulario()
    {
        if (!session('is_admin')) abort(403);

        $unidadeId = session('unidade_id');

        $backups = DB::table('materiais_backup')
            ->where('unidade_id', $unidadeId)
            ->selectRaw('backup_label, MIN(backup_at) as backup_at, COUNT(*) as total')
            ->groupBy('backup_label')
            ->orderByDesc('backup_at')
            ->limit(10)
            ->get();

        return view('admin.importar', compact('backups'));
    }

    public function importar(Request $request)
    {
        if (!session('is_admin')) abort(403);

        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:30720',
        ], [
            'csv.required' => 'Selecione o arquivo CSV.',
            'csv.mimes'    => 'O arquivo deve ser CSV ou TXT.',
            'csv.max'      => 'O arquivo não pode ultrapassar 30MB.',
        ]);

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $unidadeId = session('unidade_id');

        // ── 1. Ler e converter encoding ──────────────────────────────────────
        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
        }

        $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $header = array_shift($linhas);
        $linhas = array_filter($linhas, fn($l) => trim($l) !== '');

        if (!$this->headerValido($header)) {
            return back()->withErrors(['csv' => 'O arquivo não parece ser um CSV do SILOMS. Verifique se o arquivo contém as colunas idPatrimonio e setor (mínimo 14 colunas separadas por vírgula).']);
        }

        if (count($linhas) === 0) {
            return back()->withErrors(['csv' => 'O arquivo está vazio ou sem dados após o cabeçalho.']);
        }

        // ── 2. Backup de TODOS os materiais da unidade ───────────────────────
        $agora       = now();
        $backupLabel = $request->file('csv')->getClientOriginalName() . ' — ' . $agora->format('d/m/Y H:i');

        DB::statement("
            INSERT INTO materiais_backup
                (backup_at, backup_label, material_id, unidade_id, dependencia, unidade_implantou,
                 conta, classe, num_bmp, nomenclatura, num_serie, fcg, num_pn, num_sispat,
                 etiqueta_metalica, quantidade, valor_atualizado, valor_depreciacao, valor_liquido,
                 sigilo, data_implantacao, situacao, em_uso, funcionando, mais_informacoes,
                 responsavel_id, local_id)
            SELECT ?, ?, id, unidade_id, dependencia, unidade_implantou,
                conta, classe, num_bmp, nomenclatura, num_serie, fcg, num_pn, num_sispat,
                etiqueta_metalica, quantidade, valor_atualizado, valor_depreciacao, valor_liquido,
                sigilo, data_implantacao, situacao, em_uso, funcionando, mais_informacoes,
                responsavel_id, local_id
            FROM materiais
            WHERE unidade_id = ?
        ", [$agora, $backupLabel, $unidadeId]);

        $totalBackup = DB::table('materiais_backup')
            ->where('backup_label', $backupLabel)->count();

        // ── 3. Índice BMP → id de TODOS os materiais da unidade ─────────────
        $idxBmp = DB::table('materiais')
            ->where('unidade_id', $unidadeId)
            ->whereNotNull('num_bmp')
            ->orderBy('id')
            ->pluck('id', 'num_bmp')
            ->toArray();

        $idsAntes = DB::table('materiais')
            ->where('unidade_id', $unidadeId)
            ->pluck('id')->flip()->toArray();

        // ── 4. Processar CSV — novo formato SILOMS (vírgula, 14 colunas) ─────
        // 0:idPatrimonio  1:unidadeImplantou  2:setor  3:unidadeSetor  4:sigilo
        // 5:situacao  6:cdClasse  7:dsClasse  8:contaContabil  9:subElemento
        // 10:descricao  11:nrPn  12:nrSerie  13:dataImplantacao
        $atualizados     = 0;
        $inseridos       = 0;
        $idsMatchados    = [];
        $loteInsert      = [];
        $loteSize        = 500;
        $bmpVistos       = [];
        $setoresChecados = [];

        foreach ($linhas as $linha) {
            $c = str_getcsv(trim($linha));
            if (count($c) < 14) continue;

            $numBmp = $this->intVal($c[0]);
            if (!$numBmp) continue;
            if (isset($bmpVistos[$numBmp])) continue;

            $depCsv = $this->ns($c[2]);
            if (!$depCsv) continue;

            $bmpVistos[$numBmp] = true;

            // Auto-cria setor se esta dependência ainda não existe na unidade
            if (!isset($setoresChecados[$depCsv])) {
                $existe = DB::table('setores')
                    ->where('nome', $depCsv)
                    ->where('unidade_id', $unidadeId)
                    ->exists();

                if (!$existe) {
                    $partes = explode(' - ', $depCsv, 2);
                    $sigla  = mb_strtoupper(mb_substr(trim($partes[0]), 0, 10));
                    DB::table('setores')->insert([
                        'nome'       => $depCsv,
                        'sigla'      => $sigla,
                        'senha'      => null,
                        'senha_adm'  => null,
                        'unidade_id' => $unidadeId,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ]);
                }

                $setoresChecados[$depCsv] = true;
            }

            $csvFields = [
                'dependencia'       => $depCsv,
                'unidade_id'        => $unidadeId,
                'unidade_implantou' => $this->ns($c[1]),
                'conta'             => $this->ns($c[8]),
                'classe'            => $this->ns($c[7]),
                'num_bmp'           => $numBmp,
                'nomenclatura'      => $this->ns($c[10]),
                'num_serie'         => $this->ns($c[12]),
                'num_pn'            => $this->ns($c[11]),
                'sigilo'            => $this->ns($c[4]),
                'situacao'          => $this->ns($c[5]),
                'data_implantacao'  => $this->dateVal($c[13]),
            ];

            if (isset($idxBmp[$numBmp])) {
                $id      = $idxBmp[$numBmp];
                $changed = DB::table('materiais')->where('id', $id)->update($csvFields);
                $idsMatchados[$id] = true;
                if ($changed) $atualizados++;
            } else {
                $loteInsert[] = array_merge($csvFields, ['created_at' => $agora, 'updated_at' => $agora]);
                $inseridos++;

                if (count($loteInsert) >= $loteSize) {
                    DB::table('materiais')->insert($loteInsert);
                    $loteInsert = [];
                }
            }
        }

        if (!empty($loteInsert)) {
            DB::table('materiais')->insert($loteInsert);
        }

        // ── 5. Coleta IDs dos registros recém-inseridos ──────────────────────
        $bmpNovos = array_diff(array_keys($bmpVistos), array_keys($idxBmp));
        if (!empty($bmpNovos)) {
            $idsNovos = DB::table('materiais')
                ->where('unidade_id', $unidadeId)
                ->whereIn('num_bmp', $bmpNovos)
                ->pluck('id');
            foreach ($idsNovos as $id) {
                $idsMatchados[$id] = true;
            }
        }

        // ── 6. Exclui o que estava na unidade mas não veio no CSV ────────────
        $excluidos      = 0;
        $idsParaExcluir = array_diff(array_keys($idsAntes), array_keys($idsMatchados));
        if (!empty($idsParaExcluir)) {
            $excluidos = count($idsParaExcluir);
            DB::table('materiais')->whereIn('id', $idsParaExcluir)->delete();
        }

        return redirect()->route('admin.importar')
            ->with('resultado', [
                'backup'      => $totalBackup,
                'atualizados' => $atualizados,
                'inseridos'   => $inseridos,
                'excluidos'   => $excluidos,
                'label'       => $backupLabel,
            ]);
    }

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
        $v = trim((string)$val);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : 0.0;
    }
}
