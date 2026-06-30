<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relação de Material</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #222; margin: 0; }
        h2 { font-size: 11pt; margin: 0 0 4px 0; }
        .sub { font-size: 8pt; color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #1565c0; color: #fff; padding: 5px 6px; text-align: left; font-size: 7.5pt; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        tr:nth-child(even) td { background: #f5f8ff; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7pt; font-weight: 600; }
        .badge-success  { background: #e8f5e9; color: #2e7d32; }
        .badge-danger   { background: #ffebee; color: #c62828; }
        .badge-secondary{ background: #eceff1; color: #546e7a; }
        .badge-warning  { background: #fff8e1; color: #f57f17; }
        .badge-light    { background: #f0f0f0; color: #333; }
        .footer { margin-top: 10px; font-size: 7pt; color: #888; text-align: right; }
    </style>
</head>
<body>
    <h2><i>VilSystem</i> — Relação de Material</h2>
    <div class="sub">
        Setor: <strong>{{ $setor }}</strong> &nbsp;|&nbsp;
        Filtro: <strong>{{ $titulo }}</strong> &nbsp;|&nbsp;
        Total: <strong>{{ $materiais->count() }}</strong> &nbsp;|&nbsp;
        Gerado em: {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:7%">BMP</th>
                <th style="width:30%">Nomenclatura</th>
                <th style="width:14%">Dependência</th>
                <th style="width:10%">Local</th>
                <th style="width:12%">Responsável</th>
                <th style="width:8%">Situação</th>
                <th style="width:7%">Série</th>
                <th style="width:12%">Grupos</th>
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
                        $colors = ['Em Uso'=>'success','A'=>'success','Em Reparo'=>'danger','R'=>'danger','A Alienar'=>'secondary','D'=>'secondary'];
                        $color  = $colors[$m->situacao] ?? 'light';
                    @endphp
                    <span class="badge badge-{{ $color }}">{{ $m->situacao ?? '—' }}</span>
                </td>
                <td>{{ $m->num_serie ?? '—' }}</td>
                <td>{{ $m->selecoes->pluck('nome')->join(', ') ?: '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#888">Nenhum material encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">VilSystem — Exportado em {{ now()->format('d/m/Y H:i:s') }}</div>
</body>
</html>
