<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacaoController extends Controller
{
    public function formulario()
    {
        if (!session('is_admin')) abort(403);

        $setor     = session('setor_nome');
        $unidadeId = session('unidade_id');

        $backups = DB::table('materiais_backup')
            ->where('dependencia', $setor)->where('unidade_id', $unidadeId)
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

        $setor     = session('setor_nome');
        $unidadeId = session('unidade_id');

        // ── 1. Ler e converter encoding ──────────────────────────────────────
        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
        }

        $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $header = array_shift($linhas); // guarda cabeçalho para validação

        $linhas = array_filter($linhas, fn($l) => trim($l) !== '');

        // ── 1a. Validar cabeçalho do CSV ─────────────────────────────────────
        if (!$this->headerValido($header)) {
            return back()->withErrors(['csv' => 'O arquivo não parece ser um CSV do SILOMS. Verifique se o arquivo contém as colunas Nº BMP e Nº SISPAT (mínimo 15 colunas separadas por ponto e vírgula).']);
        }

        if (count($linhas) === 0) {
            return back()->withErrors(['csv' => 'O arquivo está vazio ou sem dados após o cabeçalho.']);
        }

        // ── 2. Backup apenas dos materiais da unidade atual ──────────────────
        $agora       = now();
        $backupLabel = $request->file('csv')->getClientOriginalName() . ' — ' . $agora->format('d/m/Y H:i');

        DB::statement("
            INSERT INTO materiais_backup
                (backup_at, backup_label, material_id, unidade_id, dependencia, conta, classe, num_bmp,
                 nomenclatura, num_serie, fcg, num_pn, num_sispat, etiqueta_metalica,
                 quantidade, valor_atualizado, valor_depreciacao, valor_liquido,
                 situacao, em_uso, funcionando, mais_informacoes, responsavel_id, local_id)
            SELECT ?, ?, id, unidade_id, dependencia, conta, classe, num_bmp,
                nomenclatura, num_serie, fcg, num_pn, num_sispat, etiqueta_metalica,
                quantidade, valor_atualizado, valor_depreciacao, valor_liquido,
                situacao, em_uso, funcionando, mais_informacoes, responsavel_id, local_id
            FROM materiais
            WHERE dependencia = ? AND unidade_id = ?
        ", [$agora, $backupLabel, $setor, $unidadeId]);

        $totalBackup = DB::table('materiais_backup')
            ->where('backup_label', $backupLabel)->count();

        // ── 3. Índice BMP → id dos materiais atuais do setor ────────────────
        // orderBy garante que duplicatas de BMP no sistema resolvem para o maior id
        $idxBmp = DB::table('materiais')
            ->where('dependencia', $setor)->where('unidade_id', $unidadeId)
            ->whereNotNull('num_bmp')
            ->orderBy('id')
            ->pluck('id', 'num_bmp')
            ->toArray();

        // Todos os IDs presentes antes da importação (para saber o que excluir)
        $idsAntes = DB::table('materiais')
            ->where('dependencia', $setor)->where('unidade_id', $unidadeId)
            ->pluck('id')->flip()->toArray();

        // ── 4. Processar CSV — chave única: Nº BMP ───────────────────────────
        $atualizados  = 0;
        $inseridos    = 0;
        $idsMatchados = [];
        $loteInsert   = [];
        $loteSize     = 500;
        $bmpVistos    = []; // evita processar BMP duplicado dentro do próprio CSV

        foreach ($linhas as $linha) {
            $c = str_getcsv(trim($linha), ';');
            if (count($c) < 15) continue;

            $numBmp = $this->intVal($c[3]);
            if (!$numBmp) continue;           // linha sem BMP é ignorada
            if (isset($bmpVistos[$numBmp])) continue; // BMP duplicado no CSV: mantém o primeiro

            // Ignora linhas de outra dependência — evita importar CSV errado
            $depCsv = $this->ns($c[0]);
            if ($depCsv && $depCsv !== $setor) continue;

            $bmpVistos[$numBmp] = true;

            $csvFields = [
                'dependencia'       => $setor,
                'unidade_id'        => $unidadeId,
                'conta'             => $this->ns($c[1]),
                'classe'            => $this->ns($c[2]),
                'num_bmp'           => $numBmp,
                'nomenclatura'      => $this->ns($c[4]),
                'num_serie'         => $this->ns($c[5]),
                'num_pn'            => $this->ns($c[6]),
                'num_sispat'        => $this->ns($c[7]),
                'fcg'               => $this->ns($c[8]),
                'etiqueta_metalica' => $this->ns($c[9]),
                'quantidade'        => $this->intVal($c[10]) ?? 1,
                'valor_atualizado'  => $this->dec($c[11]),
                'valor_depreciacao' => $this->dec($c[12]),
                'valor_liquido'     => $this->dec($c[13]),
                'situacao'          => $this->ns($c[14]),
                // updated_at excluído aqui: MySQL retorna 0 se dados idênticos
            ];

            if (isset($idxBmp[$numBmp])) {
                // BMP existe no sistema → atualiza apenas se dados mudaram
                $id = $idxBmp[$numBmp];
                $changed = DB::table('materiais')->where('id', $id)->update($csvFields);
                $idsMatchados[$id] = true;
                if ($changed) $atualizados++;
            } else {
                // BMP novo → insere
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
                ->where('dependencia', $setor)->where('unidade_id', $unidadeId)
                ->whereIn('num_bmp', $bmpNovos)
                ->pluck('id');
            foreach ($idsNovos as $id) {
                $idsMatchados[$id] = true;
            }
        }

        // ── 6. Exclui o que estava no sistema mas não veio no CSV ────────────
        $excluidos = 0;
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
        $h = mb_strtoupper($header);
        $colunas = str_getcsv($h, ';');
        return str_contains($h, 'BMP')
            && str_contains($h, 'SISPAT')
            && count($colunas) >= 15;
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

    private function dec(?string $val): float
    {
        $v = trim((string)$val);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : 0.0;
    }
}
