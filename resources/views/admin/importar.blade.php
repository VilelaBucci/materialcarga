@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-arrow-up"></i> Importar CSV (SISPAT)</h6>
    <a href="{{ route('admin.setores') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

@if(session('resultado'))
@php $r = session('resultado'); @endphp
<div class="alert alert-success alert-dismissible fade show">
    <strong><i class="bi bi-check-circle"></i> Importação concluída!</strong><br>
    <span class="small">Arquivo: <em>{{ $r['label'] }}</em></span><br>
    <div class="mt-2 row g-2">
        <div class="col-auto">
            <span class="badge bg-secondary fs-6">{{ number_format($r['backup'], 0, ',', '.') }}</span>
            <span class="small ms-1">registros no backup</span>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary fs-6">{{ number_format($r['atualizados'], 0, ',', '.') }}</span>
            <span class="small ms-1">atualizados</span>
        </div>
        <div class="col-auto">
            <span class="badge bg-success fs-6">{{ number_format($r['inseridos'], 0, ',', '.') }}</span>
            <span class="small ms-1">inseridos</span>
        </div>
        <div class="col-auto">
            <span class="badge bg-danger fs-6">{{ number_format($r['excluidos'], 0, ',', '.') }}</span>
            <span class="small ms-1">excluídos</span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">
    {{-- Formulário de importação --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-upload"></i> Selecionar arquivo
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Selecione o arquivo CSV exportado do SISPAT (delimitado por ponto e vírgula, codificação Windows-1252).
                    Todos os registros atuais serão copiados para o backup antes da importação.
                </p>

                <div class="alert alert-warning small py-2">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Atenção:</strong> esta operação <strong>substitui</strong> a relação de materiais.
                    Registros que não existirem no CSV serão excluídos do sistema.
                </div>

                <form action="{{ route('admin.importar.post') }}" method="POST" enctype="multipart/form-data" id="formImport">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Arquivo CSV</label>
                        <input type="file" name="csv" class="form-control @error('csv') is-invalid @enderror"
                            accept=".csv,.txt" required id="inputCsv">
                        @error('csv')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Máx. 30MB. Formato: <code>DEPENDENCIA;CONTA;CLASSE;Nº BMP;...</code></div>
                    </div>

                    <div id="infoArquivo" class="mb-3 d-none">
                        <div class="alert alert-info py-2 small mb-0" id="textoInfo"></div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" id="btnImportar" disabled>
                            <i class="bi bi-file-earmark-arrow-up"></i> Importar e Atualizar
                        </button>
                    </div>
                </form>

                {{-- Spinner durante importação --}}
                <div id="loadingImport" class="d-none mt-3 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="small mt-2 text-muted">Processando arquivo, aguarde. Isso pode levar alguns minutos...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Histórico de backups --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Histórico de Backups
            </div>
            <div class="card-body p-0">
                @if($backups->isEmpty())
                <p class="text-muted small p-3 mb-0">Nenhum backup realizado ainda.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Arquivo / Data</th>
                                <th class="text-end">Registros</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $bk)
                            <tr>
                                <td>
                                    <div class="small fw-semibold text-truncate" style="max-width:280px" title="{{ $bk->backup_label }}">
                                        {{ $bk->backup_label }}
                                    </div>
                                    <div class="text-muted" style="font-size:.7rem">
                                        {{ \Carbon\Carbon::parse($bk->backup_at)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="text-end align-middle">
                                    <span class="badge bg-secondary">{{ number_format($bk->total, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('inputCsv').addEventListener('change', function() {
    const file = this.files[0];
    const btn = document.getElementById('btnImportar');
    const info = document.getElementById('infoArquivo');
    const texto = document.getElementById('textoInfo');

    if (file) {
        const mb = (file.size / 1048576).toFixed(2);
        texto.textContent = `Arquivo: ${file.name} — ${mb} MB`;
        info.classList.remove('d-none');
        btn.disabled = false;
    } else {
        info.classList.add('d-none');
        btn.disabled = true;
    }
});

document.getElementById('formImport').addEventListener('submit', function() {
    document.getElementById('btnImportar').disabled = true;
    document.getElementById('loadingImport').classList.remove('d-none');
});
</script>
@endpush
