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
    <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt"></i> Locais Setoriais</h6>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoLocal">
        <i class="bi bi-plus"></i> Novo Local
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome do Local</th>
                        <th class="d-none d-md-table-cell">Setor</th>
                        <th class="text-end">Itens</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locais as $local)
                    <tr>
                        <td class="fw-semibold">{{ $local->nome }}</td>
                        <td class="d-none d-md-table-cell small text-muted">{{ $local->setor ?? '—' }}</td>
                        <td class="text-end"><span class="badge bg-secondary">{{ $local->materiais_count }}</span></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-secondary py-0"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarLocal{{ $local->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($local->materiais_count === 0)
                                <form action="{{ route('locais.destroy', $local) }}" method="POST"
                                    onsubmit="return confirm('Remover este local?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal editar --}}
                    <div class="modal fade" id="modalEditarLocal{{ $local->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Editar Local</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form action="{{ route('locais.update', $local) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <input type="text" name="nome" class="form-control" value="{{ $local->nome }}" required>
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
                    <tr><td colspan="4" class="text-center text-muted py-4">Nenhum local cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Novo Local --}}
<div class="modal fade" id="modalNovoLocal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Novo Local</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('locais.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label small fw-semibold">Nome do Local <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control" required placeholder="Ex: Sala de operações, Galpão A...">
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
