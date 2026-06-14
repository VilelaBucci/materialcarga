<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VilSystem – Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); min-height: 100vh; display:flex; }
        body > .container { margin: auto; padding: 2rem 0 calc(2rem + env(safe-area-inset-bottom, 0px)); }
        .card { border:none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
        .logo-area { background: #0d47a1; border-radius: 16px 16px 0 0; padding: 1.5rem 2rem; text-align: center; }
        .logo-area h2 { color: #fff; font-weight: 700; letter-spacing: .05rem; margin:0; }
        .logo-area small { color: rgba(255,255,255,.6); }
        .form-control, .form-select { border-radius: 8px; }
        .btn-login { border-radius: 8px; font-weight: 600; }
        .select-setor { transition: opacity .2s; }
        .select-setor.loading { opacity: .5; }
        .step-label { font-size: .7rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: .04rem; }
        @media (max-width: 767px) {
            body > .container { padding-bottom: 5rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-9 col-md-6 col-lg-5">
            <div class="card">
                <div class="logo-area">
                    <div class="mb-1"><i class="bi bi-box-seam" style="font-size:2.2rem;color:rgba(255,255,255,.9)"></i></div>
                    <h2>VilSystem</h2>
                    <small>Material de Carga Setorial</small>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3">
                            @foreach($errors->all() as $e) <div><i class="bi bi-x-circle"></i> {{ $e }}</div> @endforeach
                        </div>
                    @endif

                    {{-- Login com senha --}}
                    <form action="{{ route('login.post') }}" method="POST" id="formLogin">
                        @csrf

                        {{-- Passo 1: Unidade --}}
                        <div class="mb-2">
                            <div class="step-label mb-1">1. Unidade</div>
                            <select name="unidade_id" id="selectUnidade" class="form-select" required>
                                <option value="">Selecione a unidade...</option>
                                @foreach($unidades as $unidade)
                                    <option value="{{ $unidade->id }}"
                                        {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                        {{ $unidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Passo 2: Setor/Dependência (carregado via AJAX) --}}
                        <div class="mb-2">
                            <div class="step-label mb-1">2. Setor / Dependência</div>
                            <select name="setor_id" id="selectSetor" class="form-select select-setor" required disabled>
                                <option value="">— selecione a unidade primeiro —</option>
                            </select>
                        </div>

                        {{-- Passo 3: Senha --}}
                        <div class="mb-3">
                            <div class="step-label mb-1">3. Senha</div>
                            <input type="password" name="senha" id="inputSenha" class="form-control"
                                placeholder="Senha do setor ou admin" required autocomplete="current-password" disabled>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login" id="btnEntrar" disabled>
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </div>
                    </form>

                    <hr class="my-3">

                    {{-- Somente Leitura --}}
                    <form action="{{ route('login.soLeitura') }}" method="POST" id="formLeitura">
                        @csrf
                        <div class="mb-1">
                            <div class="step-label mb-1">Somente leitura — sem senha</div>
                            <select name="unidade_id" id="selectUnidadeLeitura" class="form-select form-select-sm mb-1">
                                <option value="">Selecione a unidade...</option>
                                @foreach($unidades as $unidade)
                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                @endforeach
                            </select>
                            <select name="setor_id" id="selectSetorLeitura" class="form-select form-select-sm" disabled>
                                <option value="">— selecione a unidade primeiro —</option>
                            </select>
                        </div>
                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-outline-secondary btn-sm" id="btnLeitura" disabled>
                                <i class="bi bi-eye"></i> Visualizar sem senha
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.nova-unidade') }}" class="text-decoration-none small text-muted">
                            <i class="bi bi-plus-circle"></i> Cadastrar nova unidade
                        </a>
                    </div>

                </div>
            </div>
            <p class="text-center mt-3" style="color:rgba(255,255,255,.5);font-size:.8rem">
                VilSystem &copy; {{ date('Y') }}
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function carregarSetores(unidadeId, targetSelectId, btnIds, callbacks) {
    const select = document.getElementById(targetSelectId);
    select.disabled = true;
    select.classList.add('loading');
    select.innerHTML = '<option>Carregando...</option>';
    btnIds.forEach(id => document.getElementById(id).disabled = true);

    if (!unidadeId) {
        select.innerHTML = '<option value="">— selecione a unidade primeiro —</option>';
        select.classList.remove('loading');
        return;
    }

    fetch('/api/unidade/' + unidadeId + '/setores')
        .then(r => r.json())
        .then(data => {
            select.classList.remove('loading');
            if (data.length === 0) {
                select.innerHTML = '<option value="">Nenhum setor cadastrado</option>';
                return;
            }
            select.innerHTML = '<option value="">Selecione o setor...</option>';
            data.forEach(s => {
                const label = (s.sigla ? s.sigla + ' — ' : '') + s.nome;
                select.innerHTML += `<option value="${s.id}">${label}</option>`;
            });
            select.disabled = false;
            if (callbacks && callbacks.onLoaded) callbacks.onLoaded();
        })
        .catch(() => {
            select.classList.remove('loading');
            select.innerHTML = '<option value="">Erro ao carregar — tente novamente</option>';
        });
}

// Form principal
const selectUnidade = document.getElementById('selectUnidade');
const selectSetor   = document.getElementById('selectSetor');
const inputSenha    = document.getElementById('inputSenha');
const btnEntrar     = document.getElementById('btnEntrar');

selectUnidade.addEventListener('change', function() {
    inputSenha.disabled = true;
    btnEntrar.disabled  = true;
    carregarSetores(this.value, 'selectSetor', ['btnEntrar']);
});

selectSetor.addEventListener('change', function() {
    const ok = this.value !== '';
    inputSenha.disabled = !ok;
    btnEntrar.disabled  = !ok;
    if (ok) inputSenha.focus();
});

// Form somente leitura
const selectUnidadeLeitura = document.getElementById('selectUnidadeLeitura');
const selectSetorLeitura   = document.getElementById('selectSetorLeitura');
const btnLeitura           = document.getElementById('btnLeitura');

selectUnidadeLeitura.addEventListener('change', function() {
    btnLeitura.disabled = true;
    carregarSetores(this.value, 'selectSetorLeitura', ['btnLeitura']);
});

selectSetorLeitura.addEventListener('change', function() {
    btnLeitura.disabled = this.value === '';
});

// Restaurar seleção ao voltar com erros (old input)
@if(old('unidade_id'))
document.addEventListener('DOMContentLoaded', function() {
    selectUnidade.value = '{{ old("unidade_id") }}';
    carregarSetores('{{ old("unidade_id") }}', 'selectSetor', ['btnEntrar'], {
        onLoaded: function() {
            const oldSetor = '{{ old("setor_id") }}';
            if (oldSetor) {
                selectSetor.value = oldSetor;
                selectSetor.dispatchEvent(new Event('change'));
            }
        }
    });
});
@endif
</script>
</body>
</html>
