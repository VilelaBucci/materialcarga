<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VilSystem – Nova Unidade</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); min-height: 100vh; padding: 2rem 0; }
        .card { border: none; border-radius: 14px; box-shadow: 0 8px 32px rgba(0,0,0,.2); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; border-radius: 14px 14px 0 0 !important; font-weight: 600; }
        .step-circle {
            width: 28px; height: 28px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem; flex-shrink: 0;
        }
        .drop-zone {
            border: 2px dashed #ced4da; border-radius: 10px;
            padding: 1.8rem; text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .drop-zone:hover, .drop-zone.over { border-color: #0d6efd; background: #f0f6ff; }
        .drop-zone.ok  { border-color: #198754; background: #f0fff4; }
        .drop-zone.err { border-color: #dc3545; background: #fff5f5; }
        .dep-badge { font-size: .75rem; padding: .3rem .6rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="text-white fw-bold mb-0"><i class="bi bi-plus-circle"></i> Cadastrar Nova Unidade</h5>
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Voltar ao Login
                </a>
            </div>

            @if(session('sucesso'))
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('sucesso') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e) <div><i class="bi bi-x-circle"></i> {{ $e }}</div> @endforeach
                </div>
            @endif

            {{-- Passo 1: Arquivo CSV --}}
            <div class="card mb-3">
                <div class="card-header py-2 d-flex align-items-center gap-2">
                    <span class="step-circle bg-primary text-white">1</span>
                    Validar arquivo CSV do SILOMS
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Selecione o arquivo CSV exportado do SILOMS/SISPAT referente a esta unidade.
                        O formato deve ser o mesmo utilizado na importação de dados (campos separados por ponto e vírgula).
                    </p>

                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('inputCsv').click()">
                        <input type="file" id="inputCsv" accept=".csv,.txt" class="d-none">
                        <i class="bi bi-file-earmark-spreadsheet fs-2 text-muted"></i>
                        <div class="mt-2 text-muted small" id="dropText">Clique ou arraste o arquivo CSV aqui</div>
                        <div id="dropStatus" class="mt-1 small fw-semibold d-none"></div>
                    </div>

                    <div id="loadingCsv" class="mt-3 d-none text-center">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="small ms-2 text-muted">Validando arquivo...</span>
                    </div>

                    <div id="csvInfo" class="mt-3 d-none">
                        <div class="alert alert-success py-2 mb-0">
                            <div class="fw-semibold mb-1"><i class="bi bi-check-circle"></i> Arquivo válido!</div>
                            <div class="small">
                                Total de registros: <strong id="csvTotal"></strong><br>
                                Dependências/Setores detectados no arquivo:
                            </div>
                            <div id="csvDeps" class="mt-2 d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>

                    <div id="csvErro" class="mt-3 d-none">
                        <div class="alert alert-danger py-2 mb-0 small">
                            <i class="bi bi-x-circle"></i> <span id="csvErroTexto"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Passo 2: Dados da unidade --}}
            <div class="card" id="cardPasso2" style="opacity:.45;pointer-events:none">
                <div class="card-header py-2 d-flex align-items-center gap-2">
                    <span class="step-circle bg-secondary text-white" id="circulo2">2</span>
                    Dados da nova unidade
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.nova-unidade.post') }}" method="POST"
                          enctype="multipart/form-data" id="formNovaUnidade">
                        @csrf

                        {{-- Input file real (dentro do form, preenchido via JS) --}}
                        <input type="file" name="csv" id="realCsvInput" class="d-none" accept=".csv,.txt">

                        <div class="alert alert-info small py-2 mb-3">
                            <i class="bi bi-info-circle"></i>
                            O <strong>Nome da Unidade</strong> é livre — pode ser o nome completo da OM ou qualquer identificador.
                            Cada valor único encontrado na coluna <em>DEPENDÊNCIA</em> do CSV se tornará um
                            <strong>setor</strong> acessível com a senha inicial que você definir abaixo.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    Nome da Unidade <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nome" id="inputNome"
                                    class="form-control @error('nome') is-invalid @enderror"
                                    value="{{ old('nome') }}" required maxlength="200"
                                    placeholder="Ex: Escola de Especialistas de Aeronáutica">
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    Senha inicial dos setores <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="senha_ini"
                                    class="form-control @error('senha_ini') is-invalid @enderror"
                                    required minlength="4" maxlength="50" autocomplete="new-password"
                                    placeholder="Mínimo 4 caracteres">
                                @error('senha_ini')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Todos os setores criados receberão esta senha. Altere depois em Administração → Senhas dos Setores.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    Senha de administrador da unidade <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="senha_adm"
                                    class="form-control @error('senha_adm') is-invalid @enderror"
                                    required minlength="6" maxlength="50" autocomplete="new-password"
                                    placeholder="Mínimo 6 caracteres">
                                @error('senha_adm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Permite entrar como admin em qualquer setor desta unidade.</div>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-warning small py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Todos os registros do CSV serão importados como dados desta nova unidade.
                                    Esta ação <strong>não afeta</strong> dados de outras unidades já cadastradas.
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-success" id="btnCriar">
                                <i class="bi bi-plus-circle"></i> Criar Unidade e Importar Dados
                            </button>
                            <div id="loadingCriar" class="d-none align-items-center gap-2">
                                <div class="spinner-border spinner-border-sm text-success"></div>
                                <span class="small text-muted">Importando dados, aguarde...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dropZone     = document.getElementById('dropZone');
const inputCsv     = document.getElementById('inputCsv');
const realCsvInput = document.getElementById('realCsvInput');
const cardPasso2   = document.getElementById('cardPasso2');
const circulo2     = document.getElementById('circulo2');

// Drag & drop
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('over');
    if (e.dataTransfer.files[0]) processarArquivo(e.dataTransfer.files[0]);
});
inputCsv.addEventListener('change', function() {
    if (this.files[0]) processarArquivo(this.files[0]);
});

function processarArquivo(file) {
    const loading = document.getElementById('loadingCsv');
    const info    = document.getElementById('csvInfo');
    const erro    = document.getElementById('csvErro');
    const status  = document.getElementById('dropStatus');

    info.classList.add('d-none');
    erro.classList.add('d-none');
    loading.classList.remove('d-none');
    status.classList.add('d-none');
    dropZone.classList.remove('ok', 'err');
    desabilitarPasso2();

    document.getElementById('dropText').textContent = file.name + ' (' + (file.size / 1048576).toFixed(2) + ' MB)';

    const form = new FormData();
    form.append('csv', file);
    form.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("admin.validar-csv") }}', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');

            if (data.valido) {
                dropZone.classList.add('ok');
                status.textContent = '✓ Arquivo válido';
                status.classList.remove('d-none', 'text-danger');
                status.classList.add('text-success');

                document.getElementById('csvTotal').textContent = data.total_linhas.toLocaleString('pt-BR');

                // Mostra dependências detectadas como badges
                const depsEl = document.getElementById('csvDeps');
                depsEl.innerHTML = '';
                data.dependencias.forEach(dep => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-primary dep-badge';
                    span.textContent = dep;
                    depsEl.appendChild(span);
                });
                info.classList.remove('d-none');

                // Copia o arquivo para o input real do form
                const dt = new DataTransfer();
                dt.items.add(file);
                realCsvInput.files = dt.files;

                habilitarPasso2();
            } else {
                dropZone.classList.add('err');
                status.textContent = '✗ Arquivo inválido';
                status.classList.remove('d-none', 'text-success');
                status.classList.add('text-danger');
                document.getElementById('csvErroTexto').textContent = data.erro;
                erro.classList.remove('d-none');
            }
        })
        .catch(() => {
            loading.classList.add('d-none');
            dropZone.classList.add('err');
            document.getElementById('csvErroTexto').textContent = 'Erro ao validar. Tente novamente.';
            erro.classList.remove('d-none');
        });
}

function habilitarPasso2() {
    cardPasso2.style.opacity       = '1';
    cardPasso2.style.pointerEvents = 'auto';
    circulo2.classList.replace('bg-secondary', 'bg-primary');
}

function desabilitarPasso2() {
    cardPasso2.style.opacity       = '0.45';
    cardPasso2.style.pointerEvents = 'none';
    circulo2.classList.replace('bg-primary', 'bg-secondary');
}

document.getElementById('formNovaUnidade').addEventListener('submit', function() {
    document.getElementById('btnCriar').disabled = true;
    const l = document.getElementById('loadingCriar');
    l.classList.remove('d-none');
    l.classList.add('d-flex');
});

// Se voltou com erros server-side, re-habilita passo 2
@if($errors->any())
habilitarPasso2();
@endif
</script>
</body>
</html>
