@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-key"></i> Administração</h6>
    <a href="{{ route('admin.importar') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
    </a>
</div>

{{-- Alterar senha do administrador --}}
<div class="card mb-3">
    <div class="card-header">
        <i class="bi bi-shield-lock"></i> Senha de Administrador da Unidade
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            <i class="bi bi-info-circle"></i>
            Esta senha permite entrar como <strong>administrador em qualquer setor</strong> desta unidade.
            Ao alterar aqui, a mudança vale para todos os setores.
        </p>
        <form action="{{ route('admin.senha.adm') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Senha atual</label>
                <input type="password" name="senha_atual" class="form-control form-control-sm @error('senha_atual') is-invalid @enderror"
                    placeholder="Senha atual" required autocomplete="current-password">
                @error('senha_atual')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Nova senha</label>
                <input type="password" name="senha_nova" class="form-control form-control-sm @error('senha_nova') is-invalid @enderror"
                    placeholder="Nova senha (mín. 6 car.)" required minlength="6" maxlength="50" autocomplete="new-password">
                @error('senha_nova')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Confirmar senha</label>
                <input type="password" name="senha_conf" class="form-control form-control-sm @error('senha_conf') is-invalid @enderror"
                    placeholder="Repita a nova senha" required maxlength="50" autocomplete="new-password">
                @error('senha_conf')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<h6 class="fw-semibold mb-2"><i class="bi bi-building"></i> Senhas dos Setores</h6>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sigla</th>
                        <th>Setor</th>
                        <th class="text-center">Senha Atual</th>
                        <th class="text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($setores as $setor)
                    <tr>
                        <td><span class="badge bg-primary">{{ $setor->sigla }}</span></td>
                        <td class="small">{{ $setor->nome }}</td>
                        <td class="text-center">
                            @if($setor->senha)
                                <span class="badge bg-success"><i class="bi bi-lock-fill"></i> Definida</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-lock"></i> Sem senha</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary py-0"
                                data-bs-toggle="modal"
                                data-bs-target="#modalSenha{{ $setor->id }}">
                                <i class="bi bi-pencil"></i> Alterar Senha
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalSenha{{ $setor->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header py-2">
                                    <h6 class="modal-title">
                                        <i class="bi bi-key"></i> Senha — {{ $setor->sigla }}
                                    </h6>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.setores.senha', $setor) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">
                                                {{ $setor->nome }}
                                            </label>
                                            <input type="text" name="senha"
                                                class="form-control form-control-sm"
                                                placeholder="Nova senha"
                                                value="{{ $setor->senha }}"
                                                required minlength="4" maxlength="50"
                                                autocomplete="off">
                                            <div class="form-text">Mín. 4 caracteres.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer py-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-check-lg"></i> Salvar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
