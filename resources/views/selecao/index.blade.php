@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-tags"></i> Grupos de Material</h6>
    <a href="{{ route('material.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<p class="text-muted small mb-3">
    Crie grupos para organizar e filtrar materiais (ex: "A Descarregar", "Para Auditoria").
    Os grupos são exclusivos do seu setor.
</p>

{{-- Criar novo grupo --}}
<div class="card mb-3">
    <div class="card-header py-2"><i class="bi bi-plus-circle"></i> Novo Grupo</div>
    <div class="card-body">
        <form action="{{ route('selecoes.store') }}" method="POST" class="d-flex gap-2">
            @csrf
            <input type="text" name="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror"
                placeholder="Nome do grupo (ex: A Descarregar)" maxlength="100" required
                value="{{ old('nome') }}" autofocus>
            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg"></i> Criar
            </button>
        </form>
    </div>
</div>

{{-- Lista de grupos --}}
<div class="card">
    <div class="card-body p-0">
        @if($selecoes->isEmpty())
            <p class="text-muted text-center py-4 mb-0">Nenhum grupo criado ainda.</p>
        @else
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th class="text-center">Materiais</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selecoes as $selecao)
                <tr>
                    <td>
                        <form action="{{ route('selecoes.update', $selecao) }}" method="POST"
                            class="d-flex gap-2 align-items-center" id="form-rename-{{ $selecao->id }}">
                            @csrf @method('PUT')
                            <input type="text" name="nome" class="form-control form-control-sm"
                                value="{{ $selecao->nome }}" maxlength="100" required style="max-width:250px">
                            <button type="submit" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('material.index', ['selecao_id' => $selecao->id]) }}"
                            class="badge bg-primary text-decoration-none">
                            {{ $selecao->materiais_count }}
                        </a>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('selecoes.destroy', $selecao) }}" method="POST"
                            onsubmit="return confirm('Remover grupo «{{ $selecao->nome }}»? Os materiais não serão excluídos.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
