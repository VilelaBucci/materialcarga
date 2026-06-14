@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-boxes"></i> Material de Carga</span>
        <span class="text-muted small">{{ $materiais->total() }} item(ns)</span>
    </div>

    {{-- Filtros --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('material.index') }}">
            <div class="row g-2">
                {{-- Busca: linha inteira --}}
                <div class="col-12">
                    <input type="text" name="busca" class="form-control form-control-sm"
                        placeholder="Buscar por nome, BMP, série, SISPAT..."
                        value="{{ request('busca') }}">
                </div>
                {{-- Demais filtros: 2 por linha no mobile --}}
                <div class="col-6 col-md-2">
                    <select name="situacao" class="form-select form-select-sm">
                        <option value="">Situação</option>
                        <option value="A" {{ request('situacao')=='A'?'selected':'' }}>Ativo</option>
                        <option value="R" {{ request('situacao')=='R'?'selected':'' }}>Em Reparo</option>
                        <option value="P" {{ request('situacao')=='P'?'selected':'' }}>Paralisado</option>
                        <option value="D" {{ request('situacao')=='D'?'selected':'' }}>Desativado</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="local_id" class="form-select form-select-sm">
                        <option value="">Local</option>
                        @foreach($locais as $local)
                            <option value="{{ $local->id }}" {{ request('local_id')==$local->id?'selected':'' }}>
                                {{ $local->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="responsavel_id" class="form-select form-select-sm">
                        <option value="">Responsável</option>
                        @foreach($responsaveis as $resp)
                            <option value="{{ $resp->id }}" {{ request('responsavel_id')==$resp->id?'selected':'' }}>
                                {{ $resp->nome }}{{ $resp->graduacao ? ' ('.$resp->graduacao.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="funcionando" class="form-select form-select-sm">
                        <option value="">Funcionando</option>
                        <option value="SIM" {{ request('funcionando')=='SIM'?'selected':'' }}>Sim</option>
                        <option value="NÃO" {{ request('funcionando')=='NÃO'?'selected':'' }}>Não</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill flex-md-grow-0 px-3">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('material.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>

            <div class="row g-2 mt-1">
                @if($verTodos)
                <div class="col-12 col-md-4">
                    <select name="dependencia" class="form-select form-select-sm">
                        <option value="">Todos os Setores</option>
                        @foreach($dependencias as $dep)
                            <option value="{{ $dep }}" {{ request('dependencia')==$dep?'selected':'' }}>
                                {{ $dep }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="sem_local" value="1" id="semLocal" {{ request('sem_local')?'checked':'' }}>
                        <label class="form-check-label small" for="semLocal">Sem local</label>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="incluir_duradouro" value="1" id="duravel" {{ request('incluir_duradouro')?'checked':'' }}>
                        <label class="form-check-label small" for="duravel">Incluir conta 87</label>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabela com scroll horizontal no mobile --}}
    <div class="card-body p-0" style="overflow:hidden">
        <div class="table-responsive" style="width:100%;-webkit-overflow-scrolling:touch">
            <table class="table table-hover table-sm mb-0" style="min-width:580px">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="white-space:nowrap">BMP</th>
                        <th>Nomenclatura</th>
                        <th style="white-space:nowrap">Nº Série</th>
                        <th style="white-space:nowrap">Local</th>
                        <th style="white-space:nowrap">Func.</th>
                        <th style="white-space:nowrap">Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materiais as $m)
                    <tr class="{{ $m->situacao ? 'situacao-'.$m->situacao : '' }}" style="cursor:pointer" onclick="window.location='{{ route('material.show', $m) }}'">
                        <td class="fw-semibold" style="white-space:nowrap">{{ $m->num_bmp ?? '—' }}</td>
                        <td style="min-width:160px">{{ Str::limit($m->nomenclatura, 40) }}</td>
                        <td class="text-muted small" style="white-space:nowrap">{{ $m->num_serie ?? '—' }}</td>
                        <td class="small" style="white-space:nowrap">{{ Str::limit($m->local->nome ?? '—', 20) }}</td>
                        <td class="text-center">
                            @if($m->funcionando === 'SIM') <i class="bi bi-check-circle text-success"></i>
                            @elseif($m->funcionando === 'NÃO') <i class="bi bi-x-circle text-danger"></i>
                            @else <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap">
                            <span class="badge bg-{{ $m->situacao_badge }}">{{ $m->situacao_label }}</span>
                        </td>
                        <td>
                            <a href="{{ route('material.show', $m) }}" class="btn btn-sm btn-outline-primary py-0" onclick="event.stopPropagation()">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nenhum material encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($materiais->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Exibindo {{ $materiais->firstItem() }}–{{ $materiais->lastItem() }} de {{ $materiais->total() }}</small>
        {{ $materiais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
