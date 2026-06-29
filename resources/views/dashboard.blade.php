@extends('layouts.app')

@push('styles')
<style>
    .card-link { text-decoration: none; color: inherit; display: block; }
    .card-clickable { transition: transform .15s, box-shadow .15s; cursor: pointer; }
    .card-link:hover .card-clickable { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,.14) !important; }
</style>
@endpush

@section('content')

{{-- Cabeçalho com unidade operante --}}
<div class="card mb-4">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="field-label mb-1"><i class="bi bi-building"></i> Unidade Operante</div>
                @if(session('unidade_nome') && session('unidade_nome') !== session('setor_nome'))
                    <div class="text-muted small">{{ session('unidade_nome') }}</div>
                @endif
                <div class="fw-bold fs-5 text-primary">
                    {{ session('setor_sigla') ? session('setor_sigla').' — ' : '' }}{{ session('setor_nome') }}
                </div>
            </div>
            @if(session('is_admin') && session('ver_todos'))
            <span class="badge bg-warning text-dark fs-6 d-flex align-items-center">
                <i class="bi bi-globe me-1"></i> Modo Global
            </span>
            @endif
        </div>
    </div>
</div>

{{-- Cards de totais (clicáveis) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index') }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-primary">{{ number_format($totais['total']) }}</div>
                <div class="small text-muted">Total</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index', ['situacao' => 'Em Uso']) }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-success">{{ number_format($totais['ativos']) }}</div>
                <div class="small text-muted">Em Uso</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index', ['situacao' => 'Em Reparo']) }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-danger">{{ number_format($totais['em_reparo']) }}</div>
                <div class="small text-muted">Em Reparo</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index', ['situacao' => 'A Alienar']) }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-warning">{{ number_format($totais['paralisados']) }}</div>
                <div class="small text-muted">A Alienar</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index', ['sem_local' => '1']) }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-secondary">{{ number_format($totais['sem_local']) }}</div>
                <div class="small text-muted">Sem Local</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('material.index', ['funcionando' => 'NÃO']) }}" class="card-link">
            <div class="card text-center p-3 card-clickable">
                <div class="fs-2 fw-bold text-danger">{{ number_format($totais['sem_funcionar']) }}</div>
                <div class="small text-muted">Não Funciona</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-2">
                <i class="bi bi-geo-alt"></i> Top 10 Locais
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Local</th><th class="text-end">Qtd</th></tr></thead>
                        <tbody>
                            @forelse($por_local as $item)
                            <tr>
                                <td>
                                    @if($item->local)
                                        <a href="{{ route('material.index', ['local_id' => $item->local_id]) }}" class="text-decoration-none">
                                            {{ $item->local->nome }}
                                        </a>
                                    @else
                                        <a href="{{ route('material.index', ['sem_local' => '1']) }}" class="text-decoration-none text-muted">
                                            Sem Local
                                        </a>
                                    @endif
                                </td>
                                <td class="text-end"><span class="badge bg-primary">{{ $item->qtd }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">Nenhum dado</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-2">
                <i class="bi bi-pie-chart"></i> Situação do Material
            </div>
            <div class="card-body">
                @php
                    $total = $totais['total'] ?: 1;
                    $bars = [
                        ['label' => 'Em Uso',    'val' => $totais['ativos'],      'color' => 'success',  'situacao' => 'Em Uso'],
                        ['label' => 'Em Reparo', 'val' => $totais['em_reparo'],   'color' => 'danger',   'situacao' => 'Em Reparo'],
                        ['label' => 'A Alienar', 'val' => $totais['paralisados'], 'color' => 'warning',  'situacao' => 'A Alienar'],
                        ['label' => 'Sem Local', 'val' => $totais['sem_local'],   'color' => 'secondary','sem_local' => '1'],
                    ];
                @endphp
                @foreach($bars as $bar)
                @php
                    $params = isset($bar['situacao']) ? ['situacao' => $bar['situacao']] : ['sem_local' => '1'];
                @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <a href="{{ route('material.index', $params) }}" class="text-decoration-none text-dark">{{ $bar['label'] }}</a>
                        <span>{{ $bar['val'] }}</span>
                    </div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar bg-{{ $bar['color'] }}" style="width:{{ round($bar['val']/$total*100) }}%"></div>
                    </div>
                </div>
                @endforeach

                <div class="mt-3 d-grid">
                    <a href="{{ route('material.index') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-boxes"></i> Ver Todo o Material
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
