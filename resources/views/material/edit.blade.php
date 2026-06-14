@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('material.show', $material) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="fw-bold">Editar Material</span>
</div>

{{-- Resumo da identificação visível só no mobile --}}
<div class="card mb-3 d-md-none">
    <div class="card-body py-2 px-3">
        <div style="font-size:.68rem" class="text-muted fw-semibold text-uppercase">Nomenclatura</div>
        <div class="fw-semibold small">{{ $material->nomenclatura }}</div>
        <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:.78rem">
            <span><span class="text-muted">BMP:</span> {{ $material->num_bmp ?? '—' }}</span>
            <span><span class="text-muted">Série:</span> {{ $material->num_serie ?? '—' }}</span>
            <span><span class="text-muted">Conta:</span> {{ $material->conta ?? '—' }}</span>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Card de identificação (somente desktop) --}}
    <div class="col-md-6 d-none d-md-block">
        <div class="card h-100">
            <div class="card-header py-2"><i class="bi bi-info-circle"></i> Identificação (somente leitura)</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="field-label">Nomenclatura</div>
                        <div class="small fw-semibold">{{ $material->nomenclatura ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Nº BMP</div>
                        <div class="small">{{ $material->num_bmp ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Nº Série</div>
                        <div class="small">{{ $material->num_serie ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Conta</div>
                        <div class="small">{{ $material->conta ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Classe</div>
                        <div class="small">{{ $material->classe ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Nº SISPAT</div>
                        <div class="small">{{ $material->num_sispat ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Dependência</div>
                        <div class="small">{{ $material->dependencia ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário editável --}}
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header py-2"><i class="bi bi-pencil"></i> Campos Editáveis</div>
            <div class="card-body">
                <form action="{{ route('material.update', $material) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="field-label">Situação</label>
                        <select name="situacao" class="form-select form-select-sm">
                            <option value="">— Indefinida —</option>
                            <option value="A" {{ $material->situacao=='A'?'selected':'' }}>A</option>
                            <option value="R" {{ $material->situacao=='R'?'selected':'' }}>R</option>
                            <option value="P" {{ $material->situacao=='P'?'selected':'' }}>P</option>
                            <option value="D" {{ $material->situacao=='D'?'selected':'' }}>D</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="field-label">Em Uso</label>
                            <select name="em_uso" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="SIM" {{ $material->em_uso=='SIM'?'selected':'' }}>Sim</option>
                                <option value="NÃO" {{ $material->em_uso=='NÃO'?'selected':'' }}>Não</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="field-label">Funcionando</label>
                            <select name="funcionando" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="SIM" {{ $material->funcionando=='SIM'?'selected':'' }}>Sim</option>
                                <option value="NÃO" {{ $material->funcionando=='NÃO'?'selected':'' }}>Não</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="field-label">Local</label>
                        <select name="local_id" class="form-select form-select-sm">
                            <option value="">— Sem Local —</option>
                            @foreach($locais as $local)
                                <option value="{{ $local->id }}" {{ $material->local_id==$local->id?'selected':'' }}>
                                    {{ $local->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="field-label">Responsável</label>
                        <select name="responsavel_id" class="form-select form-select-sm">
                            <option value="">— Sem Responsável —</option>
                            @foreach($responsaveis as $resp)
                                <option value="{{ $resp->id }}" {{ $material->responsavel_id==$resp->id?'selected':'' }}>
                                    {{ $resp->nome }} ({{ $resp->graduacao }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="field-label">Observações</label>
                        <textarea name="mais_informacoes" class="form-control form-control-sm" rows="4"
                            placeholder="Informações adicionais sobre este material...">{{ old('mais_informacoes', $material->mais_informacoes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Salvar
                        </button>
                        <a href="{{ route('material.show', $material) }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
