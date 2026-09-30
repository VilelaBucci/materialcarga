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

                    {{-- Login: com senha entra com as permissões do setor; sem senha, somente leitura --}}
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
                            <input type="search" id="filtroSetor" class="form-control form-control-sm mb-1"
                                placeholder="Filtrar: digite parte do nome ou da sigla"
                                aria-label="Filtrar setores" autocomplete="off" disabled>
                            <select name="setor_id" id="selectSetor" class="form-select select-setor" required disabled>
                                <option value="">— selecione a unidade primeiro —</option>
                            </select>
                        </div>

                        {{-- Passo 3: Senha (opcional) --}}
                        <div class="mb-3">
                            <div class="step-label mb-1">3. Senha (opcional)</div>
                            <input type="password" name="senha" id="inputSenha" class="form-control"
                                placeholder="Senha do setor ou admin" autocomplete="current-password" disabled>
                            <div class="form-text" id="dicaSenha">Deixe em branco para entrar somente para leitura.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login" id="btnEntrar" disabled>
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
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
const selectUnidade = document.getElementById('selectUnidade');
const filtroSetor   = document.getElementById('filtroSetor');
const selectSetor   = document.getElementById('selectSetor');
const inputSenha    = document.getElementById('inputSenha');
const dicaSenha     = document.getElementById('dicaSenha');
const btnEntrar     = document.getElementById('btnEntrar');

// Opção fixa no topo da lista: admin entra direto vendo todos os setores da unidade
const TODOS = 'todos';

// Setores da unidade escolhida: { id, label, busca }
let setores = [];

// Minúsculas e sem acento, para o filtro achar "secao" em "Seção"
function normalizar(texto) {
    return texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
}

// Senha e botão só liberam com setor escolhido. Sem senha o botão avisa que a entrada é somente leitura;
// em todos os setores a senha admin é obrigatória.
function atualizarBotao() {
    const setorOk = selectSetor.value !== '';
    const todos   = selectSetor.value === TODOS;
    const semSenha = inputSenha.value === '';

    inputSenha.disabled    = !setorOk;
    btnEntrar.disabled     = !setorOk || (todos && semSenha);
    inputSenha.placeholder = todos ? 'Senha admin da unidade' : 'Senha do setor ou admin';
    dicaSenha.textContent  = todos
        ? 'Todos os setores exige a senha admin da unidade.'
        : 'Deixe em branco para entrar somente para leitura.';

    if (todos) {
        btnEntrar.innerHTML = '<i class="bi bi-globe"></i> Entrar em todos os setores';
    } else {
        btnEntrar.innerHTML = semSenha
            ? '<i class="bi bi-eye"></i> Entrar somente leitura'
            : '<i class="bi bi-box-arrow-in-right"></i> Entrar';
    }
}

// Monta a lista com os setores que contêm todas as palavras do filtro (filtro vazio = todos).
// Recria as opções em vez de escondê-las porque o Safari do iPhone ignora <option> oculto.
function mostrarSetores() {
    const palavras    = normalizar(filtroSetor.value).split(/\s+/).filter(Boolean);
    const lista       = setores.filter(s => palavras.every(p => s.busca.includes(p)));
    const selecionado = selectSetor.value;

    let titulo = 'Selecione o setor...';
    if (palavras.length) {
        titulo = lista.length
            ? `Selecione o setor... (${lista.length} de ${setores.length})`
            : 'Nenhum setor encontrado';
    }

    selectSetor.options.length = 0;
    selectSetor.add(new Option(titulo, ''));
    selectSetor.add(new Option('★ Todos os setores (senha admin da unidade)', TODOS));
    lista.forEach(s => selectSetor.add(new Option(s.label, s.id)));

    // Mantém a escolha se ela continua na lista; se o filtro deixou um só setor, já seleciona
    if (selecionado === TODOS || lista.some(s => String(s.id) === selecionado)) {
        selectSetor.value = selecionado;
    } else if (palavras.length && lista.length === 1) {
        selectSetor.value = lista[0].id;
    }
    atualizarBotao();
}

function carregarSetores(unidadeId, aoCarregar) {
    setores = [];
    filtroSetor.value    = '';
    filtroSetor.disabled = true;
    selectSetor.disabled = true;
    selectSetor.classList.add('loading');
    selectSetor.innerHTML = '<option value="">Carregando...</option>';
    atualizarBotao();

    if (!unidadeId) {
        selectSetor.innerHTML = '<option value="">— selecione a unidade primeiro —</option>';
        selectSetor.classList.remove('loading');
        return;
    }

    fetch('/api/unidade/' + unidadeId + '/setores')
        .then(r => r.json())
        .then(data => {
            selectSetor.classList.remove('loading');
            if (data.length === 0) {
                selectSetor.innerHTML = '<option value="">Nenhum setor cadastrado</option>';
                return;
            }
            setores = data.map(s => {
                const label = (s.sigla ? s.sigla + ' — ' : '') + s.nome;
                return { id: s.id, label: label, busca: normalizar(label) };
            });
            mostrarSetores();
            filtroSetor.disabled = false;
            selectSetor.disabled = false;
            if (aoCarregar) aoCarregar();
        })
        .catch(() => {
            selectSetor.classList.remove('loading');
            selectSetor.innerHTML = '<option value="">Erro ao carregar — tente novamente</option>';
        });
}

selectUnidade.addEventListener('change', function() {
    carregarSetores(this.value);
});

filtroSetor.addEventListener('input', mostrarSetores);

// Enter no filtro não envia o formulário: vai para a senha se já há setor escolhido, senão para a lista
filtroSetor.addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    (selectSetor.value !== '' ? inputSenha : selectSetor).focus();
});

selectSetor.addEventListener('change', function() {
    atualizarBotao();
    if (this.value !== '') inputSenha.focus();
});

inputSenha.addEventListener('input', atualizarBotao);

atualizarBotao();

// Restaurar seleção ao voltar com erros (old input)
@if(old('unidade_id'))
document.addEventListener('DOMContentLoaded', function() {
    selectUnidade.value = '{{ old("unidade_id") }}';
    carregarSetores('{{ old("unidade_id") }}', function() {
        const oldSetor = '{{ old("setor_id") }}';
        if (oldSetor) {
            selectSetor.value = oldSetor;
            selectSetor.dispatchEvent(new Event('change'));
        }
    });
});
@endif
</script>
</body>
</html>
