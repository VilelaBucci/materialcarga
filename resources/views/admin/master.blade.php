@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-shield-fill-check fs-4 text-danger"></i>
    <h6 class="mb-0 fw-bold">Senha Master</h6>
    <span class="badge bg-danger ms-1">Acesso Total</span>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="alert alert-warning py-2 small mb-3">
            <i class="bi bi-exclamation-triangle"></i>
            A senha master concede acesso de administrador a <strong>qualquer unidade e setor</strong> do sistema.
            Guarde-a com segurança.
        </div>

        <div class="card">
            <div class="card-header py-2">
                <i class="bi bi-key-fill"></i> Alterar Senha Master
            </div>
            <div class="card-body">
                <form action="{{ route('master.atualizar') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Nova senha master</label>
                        <input type="password" name="senha_nova"
                            class="form-control @error('senha_nova') is-invalid @enderror"
                            placeholder="Mínimo 8 caracteres" required minlength="8" maxlength="100"
                            autocomplete="new-password">
                        @error('senha_nova')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Confirmar nova senha</label>
                        <input type="password" name="senha_conf"
                            class="form-control @error('senha_conf') is-invalid @enderror"
                            placeholder="Repita a nova senha" required minlength="8" maxlength="100"
                            autocomplete="new-password">
                        @error('senha_conf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-shield-fill-check"></i> Atualizar Senha Master
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
