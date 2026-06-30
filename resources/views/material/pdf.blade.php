<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relação de Material — {{ $titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9pt; color: #222; margin: 0; padding: 0; background: #fff; }

        /* ── Barra de impressão (some ao imprimir) ── */
        .print-bar {
            background: #1565c0; color: #fff; padding: 10px 20px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px;
        }
        .print-bar h1 { font-size: 13pt; margin: 0; }
        .print-bar small { opacity: .8; font-size: 8pt; }
        .btn-print {
            background: #fff; color: #1565c0; border: none; border-radius: 6px;
            padding: 8px 20px; font-size: 10pt; font-weight: 700; cursor: pointer;
            white-space: nowrap;
        }
        .btn-print:hover { background: #e3f2fd; }

        /* ── Conteúdo ── */
        .content { padding: 12px 18px; }
        .meta { font-size: 8pt; color: #555; margin-bottom: 10px; }
        .meta strong { color: #222; }

        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        thead th {
            background: #1565c0; color: #fff;
            padding: 5px 6px; text-align: left;
            border: 1px solid #1040a0;
        }
        tbody td {
            padding: 4px 6px; border: 1px solid #ddd; vertical-align: top;
        }
        tbody tr:nth-child(even) td { background: #f0f6ff; }

        .badge {
            display: inline-block; padding: 2px 6px; border-radius: 4px;
            font-size: 7pt; font-weight: 700; white-space: nowrap;
        }
        .bg-success  { background: #e8f5e9; color: #2e7d32; }
        .bg-danger   { background: #ffebee; color: #c62828; }
        .bg-secondary{ background: #eceff1; color: #546e7a; }
        .bg-warning  { background: #fff8e1; color: #b45309; }
        .bg-light    { background: #f0f0f0; color: #333; }

        .footer-print { margin-top: 12px; font-size: 7pt; color: #999; text-align: right; }

        /* ── Impressão ── */
        @media print {
            .print-bar { display: none !important; }
            .content { padding: 0; }
            body { font-size: 8pt; }
            thead th { background: #1565c0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) td { background: #f0f6ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        @page { size: A4 landscape; margin: 10mm 8mm; }
    </style>
</head>
<body>

<div class="print-bar">
    <div>
        <h1>VilSystem — Relação de Material</h1>
        <small>{{ $titulo }} &nbsp;|&nbsp; {{ $materiais->count() }} registro(s) &nbsp;|&nbsp; Gerado em {{ now()->format('d/m/Y H:i') }}</small>
    </div>
    <button class="btn-print" onclick="window.print()">
        🖨️ Imprimir / Salvar PDF
    </button>
</div>

<div class="content">
    <div class="meta">
        Setor: <strong>{{ $setor }}</strong> &nbsp;&nbsp;
        Filtro: <strong>{{ $titulo }}</strong> &nbsp;&nbsp;
        Total: <strong>{{ $materiais->count() }}</strong>
        @if($materiais->count() >= 2000)
            &nbsp;<span style="color:#c62828">(limitado a 2.000 registros — aplique filtros para reduzir)</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:6%">BMP</th>
                <th style="width:28%">Nomenclatura</th>
                <th style="width:14%">Dependência</th>
                <th style="width:10%">Local</th>
                <th style="width:12%">Responsável</th>
                <th style="width:7%">Situação</th>
                <th style="width:8%">Nº Série</th>
                <th style="width:15%">Grupos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materiais as $m)
            <tr>
                <td>{{ $m->num_bmp ?? '—' }}</td>
                <td>{{ $m->nomenclatura }}</td>
                <td>{{ $m->dependencia ?? '—' }}</td>
                <td>{{ $m->local->nome ?? '—' }}</td>
                <td>{{ $m->responsavel ? trim(($m->responsavel->graduacao ? $m->responsavel->graduacao.' ' : '').$m->responsavel->nome) : '—' }}</td>
                <td>
                    @php
                        $badge = match(true) {
                            in_array($m->situacao, ['A','Em Uso'])    => 'success',
                            in_array($m->situacao, ['R','Em Reparo']) => 'danger',
                            in_array($m->situacao, ['D','A Alienar']) => 'secondary',
                            default => 'light',
                        };
                    @endphp
                    <span class="badge bg-{{ $badge }}">{{ $m->situacao ?? '—' }}</span>
                </td>
                <td>{{ $m->num_serie ?? '—' }}</td>
                <td>{{ $m->selecoes->pluck('nome')->join(', ') ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#999;padding:20px">Nenhum material encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-print">VilSystem · Exportado em {{ now()->format('d/m/Y H:i:s') }}</div>
</div>

</body>
</html>
