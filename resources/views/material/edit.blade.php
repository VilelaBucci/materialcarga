@extends('layouts.app')

@push('scripts')
<script>
function ajaxCreate(url, data, selectId, afterInsert) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(afterInsert)
    .catch(() => alert('Erro ao criar. Tente novamente.'));
}

function addOptionAndSelect(selectId, id, label) {
    const sel = document.getElementById(selectId);
    const opt = new Option(label, id, true, true);
    sel.add(opt);
}

// ── Local ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnSalvarLocal').addEventListener('click', function () {
        const nome = document.getElementById('novoLocalNome').value.trim();
        if (!nome) return;
        ajaxCreate('{{ route('locais.store') }}', {nome}, 'local_id', function(data) {
            addOptionAndSelect('local_id', data.id, data.nome);
            bootstrap.Modal.getInstance(document.getElementById('modalNovoLocal')).hide();
            document.getElementById('novoLocalNome').value = '';
        });
    });

    document.getElementById('btnSalvarResp').addEventListener('click', function () {
        const nome      = document.getElementById('novoRespNome').value.trim();
        const graduacao = document.getElementById('novoRespGrad').value.trim();
        if (!nome) return;
        ajaxCreate('{{ route('responsaveis.store') }}', {nome, graduacao}, 'responsavel_id', function(data) {
            addOptionAndSelect('responsavel_id', data.id, data.nome);
            bootstrap.Modal.getInstance(document.getElementById('modalNovoResp')).hide();
            document.getElementById('novoRespNome').value = '';
            document.getElementById('novoRespGrad').value = '';
        });
    });
});
</script>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('material.show', $material) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="fw-bold">Editar Material</span>
</div>

{{-- Mobile: resumo --}}
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
    {{-- Identificação (desktop) --}}
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
                        <div class="field-label">Conta Contábil</div>
                        <div class="small">{{ $material->conta ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Classe</div>
                        <div class="small">{{ $material->classe ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Situação (SILOMS)</div>
                        <div><span class="badge bg-{{ $material->situacao_badge }}">{{ $material->situacao_label }}</span></div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Dependência</div>
                        <div class="small">{{ $material->dependencia ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header py-2"><i class="bi bi-pencil"></i> Campos Editáveis</div>
            <div class="card-body">
                <form action="{{ route('material.update', $material) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Local --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="field-label mb-0">Local</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#modalNovoLocal">
                                <i class="bi bi-plus-circle"></i> Novo local
                            </button>
                        </div>
                        <select name="local_id" id="local_id" class="form-select form-select-sm">
                            <option value="">— Sem Local —</option>
                            @foreach($locais as $local)
                                <option value="{{ $local->id }}" {{ $material->local_id == $local->id ? 'selected' : '' }}>
                                    {{ $local->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Responsável --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="field-label mb-0">Responsável</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#modalNovoResp">
                                <i class="bi bi-plus-circle"></i> Novo responsável
                            </button>
                        </div>
                        <select name="responsavel_id" id="responsavel_id" class="form-select form-select-sm">
                            <option value="">— Sem Responsável —</option>
                            @foreach($responsaveis as $resp)
                                <option value="{{ $resp->id }}" {{ $material->responsavel_id == $resp->id ? 'selected' : '' }}>
                                    {{ trim(($resp->graduacao ? $resp->graduacao.' ' : '').$resp->nome) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Em Uso / Funcionando --}}
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

                    {{-- Grupos --}}
                    @if($selecoes->isNotEmpty())
                    <div class="mb-3">
                        <label class="field-label">Grupos</label>
                        <div class="border rounded p-2" style="max-height:130px;overflow-y:auto">
                            @foreach($selecoes as $sel)
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" type="checkbox"
                                    name="selecoes[]" value="{{ $sel->id }}"
                                    id="sel{{ $sel->id }}"
                                    {{ $material->selecoes->contains($sel->id) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sel{{ $sel->id }}">{{ $sel->nome }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-text">
                            <a href="{{ route('selecoes.index') }}" target="_blank">
                                <i class="bi bi-pencil-square"></i> Gerenciar grupos
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="field-label">Grupos</label>
                        <div class="form-text">
                            <a href="{{ route('selecoes.index') }}" target="_blank">
                                <i class="bi bi-plus-circle"></i> Criar grupos para este setor
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Observações --}}
                    <div class="mb-3">
                        <label class="field-label">Observações</label>
                        <textarea name="mais_informacoes" class="form-control form-control-sm" rows="4"
                            placeholder="Informações adicionais...">{{ old('mais_informacoes', $material->mais_informacoes) }}</textarea>
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

{{-- Modal: Novo Local --}}
<div class="modal fade" id="modalNovoLocal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-geo-alt"></i> Novo Local</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-semibold">Nome do local</label>
                <input type="text" id="novoLocalNome" class="form-control form-control-sm"
                    placeholder="Ex: Sala 12, Hangar 2..." maxlength="200" autocomplete="off">
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btnSalvarLocal">
                    <i class="bi bi-check-lg"></i> Criar e Selecionar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Novo Responsável --}}
<div class="modal fade" id="modalNovoResp" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-person-badge"></i> Novo Responsável</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Graduação <span class="text-muted">(opcional)</span></label>
                    <input type="text" id="novoRespGrad" class="form-control form-control-sm"
                        placeholder="Ex: Cel, Maj, Sgt..." maxlength="10" autocomplete="off">
                </div>
                <div>
                    <label class="form-label small fw-semibold">Nome completo</label>
                    <input type="text" id="novoRespNome" class="form-control form-control-sm"
                        placeholder="Nome..." maxlength="200" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btnSalvarResp">
                    <i class="bi bi-check-lg"></i> Criar e Selecionar
                </button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
