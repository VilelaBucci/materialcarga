@extends('layouts.app')

@section('content')

{{-- Cabeçalho: voltar + nomenclatura + botão editar --}}
<div class="mb-3">
    <div class="d-flex align-items-start gap-2">
        <a href="{{ route('material.index') }}" class="btn btn-sm btn-outline-secondary flex-shrink-0 mt-1">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:1rem;line-height:1.3">{{ $material->nomenclatura }}</div>
            @if(session('pode_editar'))
            <a href="{{ route('material.edit', $material) }}" class="btn btn-primary btn-sm mt-2 w-100 w-md-auto">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Ficha de identificação --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header py-2"><i class="bi bi-info-circle"></i> Identificação</div>
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
                        <div class="field-label">Nº PN</div>
                        <div class="small">{{ $material->num_pn ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Nº SISPAT</div>
                        <div class="small">{{ $material->num_sispat ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Etiqueta Metálica</div>
                        <div class="small">{{ $material->etiqueta_metalica ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">FCG</div>
                        <div class="small">{{ $material->fcg ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Conta</div>
                        <div class="small">{{ $material->conta ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Classe</div>
                        <div class="small">{{ $material->classe ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Quantidade</div>
                        <div class="small">{{ $material->quantidade ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="field-label">Dependência</div>
                        <div class="small">{{ $material->dependencia ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status e local --}}
    <div class="col-12 col-lg-6">
        <div class="card mb-3">
            <div class="card-header py-2"><i class="bi bi-activity"></i> Status</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="field-label">Situação</div>
                        <div><span class="badge bg-{{ $material->situacao_badge }}">{{ $material->situacao_label }}</span></div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Em Uso</div>
                        <div>
                            @if($material->em_uso === 'SIM') <span class="badge bg-success">Sim</span>
                            @elseif($material->em_uso === 'NÃO') <span class="badge bg-secondary">Não</span>
                            @else <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Funcionando</div>
                        <div>
                            @if($material->funcionando === 'SIM') <span class="badge bg-success">Sim</span>
                            @elseif($material->funcionando === 'NÃO') <span class="badge bg-danger">Não</span>
                            @else <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Local</div>
                        <div class="small">{{ $material->local->nome ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="field-label">Responsável</div>
                        <div class="small">
                            @if($material->responsavel)
                                {{ $material->responsavel->nome }}
                                <span class="text-muted">({{ $material->responsavel->graduacao }})</span>
                            @else —
                            @endif
                        </div>
                    </div>
                    @if($material->mais_informacoes)
                    <div class="col-12">
                        <div class="field-label">Observações</div>
                        <div class="small">{{ $material->mais_informacoes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Valores --}}
        <div class="card">
            <div class="card-header py-2"><i class="bi bi-currency-dollar"></i> Valores</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="field-label">Valor Atualizado</div>
                        <div class="small">R$ {{ number_format($material->valor_atualizado, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Depreciação Acum.</div>
                        <div class="small">R$ {{ number_format($material->valor_depreciacao, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Valor Líquido</div>
                        <div class="small fw-bold">R$ {{ number_format($material->valor_liquido, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fotos --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-camera"></i> Fotos ({{ $material->fotos->count() }})</span>
                @if(session('pode_editar'))
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFoto">
                    <i class="bi bi-camera-fill"></i> Adicionar
                </button>
                @endif
            </div>
            <div class="card-body">
                @if($material->fotos->isEmpty())
                    <p class="text-muted small mb-0">Nenhuma foto registrada.</p>
                @else
                <div class="row g-2">
                    @foreach($material->fotos as $foto)
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="position-relative">
                            <a href="{{ Storage::url($foto->caminho) }}" target="_blank">
                                <img src="{{ Storage::url($foto->caminho) }}" class="img-fluid rounded" style="height:120px;width:100%;object-fit:cover;">
                            </a>
                            <span class="badge {{ $foto->tipo === 'material' ? 'bg-primary' : 'bg-success' }} position-absolute top-0 start-0 m-1" style="font-size:.65rem">
                                {{ $foto->tipo === 'material' ? 'Material' : 'Local' }}
                            </span>
                            @if(session('pode_editar'))
                            <form action="{{ route('material.foto.delete', $foto) }}" method="POST" class="position-absolute top-0 end-0 m-1"
                                onsubmit="return confirm('Remover foto?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm py-0 px-1"><i class="bi bi-trash" style="font-size:.7rem"></i></button>
                            </form>
                            @endif
                            @if($foto->descricao)
                            <div class="small text-muted mt-1">{{ $foto->descricao }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reparos --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tools"></i> Reparos ({{ $material->reparos->count() }})</span>
                @if(session('pode_editar'))
                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalReparo">
                    <i class="bi bi-plus"></i> Registrar
                </button>
                @endif
            </div>
            <div class="card-body p-0" style="overflow:hidden">
                @if($material->reparos->isEmpty())
                    <p class="text-muted small p-3 mb-0">Nenhum reparo registrado.</p>
                @else
                <div class="table-responsive" style="width:100%;-webkit-overflow-scrolling:touch">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Descrição</th>
                                <th style="white-space:nowrap">Início</th>
                                <th style="white-space:nowrap">Conclusão</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($material->reparos as $reparo)
                            <tr>
                                <td class="small" style="max-width:200px;word-break:break-word">{{ $reparo->descricao }}</td>
                                <td class="small" style="white-space:nowrap">{{ $reparo->data_inicio->format('d/m/Y') }}</td>
                                <td class="small" style="white-space:nowrap">{{ $reparo->data_conclusao ? $reparo->data_conclusao->format('d/m/Y') : '—' }}</td>
                                <td><span class="badge bg-{{ $reparo->status_badge }}">{{ $reparo->status_label }}</span></td>
                                <td>
                                    @if(session('pode_editar') && $reparo->status === 'em_andamento')
                                    <form action="{{ route('material.reparo.concluir', $reparo) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-success py-0" style="white-space:nowrap">Concluir</button>
                                    </form>
                                    @endif
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

{{-- Modal Foto --}}
@if(session('pode_editar'))
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Adicionar Foto</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('material.foto.upload', $material) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="field-label">Foto <span class="text-danger">*</span></label>
                        <input type="file" name="foto" class="form-control" accept="image/*" capture="environment" required>
                        <div class="form-text">Use a câmera do celular ou escolha um arquivo. Máx 10MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="material">Foto do Material</option>
                            <option value="local">Foto do Local</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control" maxlength="200" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reparo --}}
<div class="modal fade" id="modalReparo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Registrar Reparo</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('material.reparo.store', $material) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="field-label">Descrição do Problema <span class="text-danger">*</span></label>
                        <textarea name="descricao" class="form-control" rows="3" required placeholder="Descreva o problema que gerou o reparo..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Data de Início <span class="text-danger">*</span></label>
                        <input type="date" name="data_inicio" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2" placeholder="Informações adicionais..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-tools"></i> Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
