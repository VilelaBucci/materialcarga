@extends('layouts.app')

@section('content')
@if(session('sucesso'))
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('sucesso') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge"></i> Responsáveis</h6>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoResp">
        <i class="bi bi-plus"></i> Novo Responsável
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Grad.</th>
                        <th class="d-none d-md-table-cell">Especialidade</th>
                        <th class="text-end">Itens</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responsaveis as $resp)
                    <tr>
                        <td class="fw-semibold">{{ $resp->nome }}</td>
                        <td><span class="badge bg-secondary">{{ $resp->graduacao ?? '—' }}</span></td>
                        <td class="d-none d-md-table-cell small text-muted">{{ $resp->especialidade ?? '—' }}</td>
                        <td class="text-end"><span class="badge bg-secondary">{{ $resp->materiais_count }}</span></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-secondary py-0"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditResp{{ $resp->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($resp->materiais_count === 0)
                                <form action="{{ route('responsaveis.destroy', $resp) }}" method="POST"
                                    onsubmit="return confirm('Remover este responsável?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditResp{{ $resp->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Editar Responsável</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form action="{{ route('responsaveis.update', $resp) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Nome</label>
                                            <input type="text" name="nome" class="form-control form-control-sm" value="{{ $resp->nome }}" required>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Graduação</label>
                                                <input type="text" name="graduacao" class="form-control form-control-sm" value="{{ $resp->graduacao }}" maxlength="10">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Especialidade</label>
                                                <input type="text" name="especialidade" class="form-control form-control-sm" value="{{ $resp->especialidade }}" maxlength="10">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum responsável cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoResp" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Novo Responsável</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('responsaveis.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" required placeholder="Nome completo ou apelido">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Graduação</label>
                            <input type="text" name="graduacao" class="form-control form-control-sm" maxlength="10" placeholder="Ex: 1S, SO, CB">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Especialidade</label>
                            <input type="text" name="especialidade" class="form-control form-control-sm" maxlength="10" placeholder="Ex: BMA, BET">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
