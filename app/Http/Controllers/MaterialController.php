<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Local;
use App\Models\Responsavel;
use App\Models\Selecao;
use App\Models\Foto;
use App\Models\Reparo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $setor     = session('setor_nome');
        $setorId   = session('setor_id');
        $unidadeId = session('unidade_id');
        $isAdmin   = session('is_admin', false);
        $verTodos  = $isAdmin && session('ver_todos', false);

        $query = Material::with(['local', 'responsavel'])
            ->where('unidade_id', $unidadeId)
            ->when(!$verTodos, fn($q) => $q->where('dependencia', $setor));

        if ($verTodos && $request->filled('dependencia')) {
            $query->where('dependencia', $request->dependencia);
        }

        if ($request->filled('busca')) {
            $busca = '%' . $request->busca . '%';
            $query->where(function($q) use ($busca) {
                $q->where('nomenclatura', 'like', $busca)
                  ->orWhere('num_bmp', 'like', $busca)
                  ->orWhere('num_serie', 'like', $busca);
            });
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('local_id')) {
            $query->where('local_id', $request->local_id);
        }

        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        if ($request->filled('funcionando')) {
            $query->where('funcionando', $request->funcionando);
        }

        if ($request->filled('sem_local')) {
            $query->whereNull('local_id');
        }

        if ($request->filled('selecao_id')) {
            $query->whereHas('selecoes', fn($q) => $q->where('selecoes.id', $request->selecao_id));
        }

        if (!$request->boolean('incluir_duradouro')) {
            $query->where('conta', 'not like', '%87 - MATERIAL DE CONSUMO DE USO DURADOURO%');
        }

        $materiais = $query->orderBy('num_bmp')->paginate(50)->withQueryString();

        $locais = Local::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->orderBy('nome')->get();

        $responsaveis = Responsavel::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->orderBy('nome')->get();

        $selecoes = Selecao::where('setor_id', $setorId)->orderBy('nome')->get();

        $dependencias = $verTodos
            ? DB::table('materiais')
                ->where('unidade_id', $unidadeId)
                ->whereNotNull('dependencia')
                ->distinct()->orderBy('dependencia')->pluck('dependencia')
            : collect();

        return view('material.index', compact(
            'materiais', 'locais', 'responsaveis', 'selecoes', 'dependencias', 'isAdmin', 'verTodos'
        ));
    }

    public function pdf(Request $request)
    {
        $setor     = session('setor_nome');
        $setorId   = session('setor_id');
        $unidadeId = session('unidade_id');
        $isAdmin   = session('is_admin', false);
        $verTodos  = $isAdmin && session('ver_todos', false);

        $query = Material::with(['local', 'responsavel', 'selecoes'])
            ->where('unidade_id', $unidadeId)
            ->when(!$verTodos, fn($q) => $q->where('dependencia', $setor));

        if ($verTodos && $request->filled('dependencia')) {
            $query->where('dependencia', $request->dependencia);
        }
        if ($request->filled('busca')) {
            $busca = '%' . $request->busca . '%';
            $query->where(function($q) use ($busca) {
                $q->where('nomenclatura', 'like', $busca)->orWhere('num_bmp', 'like', $busca)->orWhere('num_serie', 'like', $busca);
            });
        }
        if ($request->filled('situacao'))     $query->where('situacao', $request->situacao);
        if ($request->filled('local_id'))     $query->where('local_id', $request->local_id);
        if ($request->filled('responsavel_id')) $query->where('responsavel_id', $request->responsavel_id);
        if ($request->filled('funcionando'))  $query->where('funcionando', $request->funcionando);
        if ($request->filled('sem_local'))    $query->whereNull('local_id');
        if ($request->filled('selecao_id')) {
            $query->whereHas('selecoes', fn($q) => $q->where('selecoes.id', $request->selecao_id));
        }
        if (!$request->boolean('incluir_duradouro')) {
            $query->where('conta', 'not like', '%87 - MATERIAL DE CONSUMO DE USO DURADOURO%');
        }

        $materiais  = $query->orderBy('num_bmp')->limit(2000)->get();
        $selecaoNome = $request->filled('selecao_id')
            ? Selecao::find($request->selecao_id)?->nome
            : null;
        $titulo = $selecaoNome ?? ($request->filled('situacao') ? $request->situacao : 'Material de Carga');

        $pdf = Pdf::loadView('material.pdf', compact('materiais', 'titulo', 'setor', 'verTodos'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('relacao-' . now()->format('Y-m-d') . '.pdf');
    }

    public function show(Material $material)
    {
        $material->load(['local', 'responsavel', 'fotos', 'reparos', 'selecoes']);
        return view('material.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $setor    = session('setor_nome');
        $setorId  = session('setor_id');
        $isAdmin  = session('is_admin', false);
        $verTodos = $isAdmin && session('ver_todos', false);

        $locais = Local::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->orderBy('nome')->get();

        $responsaveis = Responsavel::when(!$verTodos, fn($q) => $q->where('setor', $setor))
            ->orderBy('nome')->get();

        $selecoes = Selecao::where('setor_id', $setorId)->orderBy('nome')->get();

        $material->load('selecoes');

        return view('material.edit', compact('material', 'locais', 'responsaveis', 'selecoes'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'local_id'        => 'nullable|exists:locais,id',
            'responsavel_id'  => 'nullable|exists:responsaveis,id',
            'em_uso'          => 'nullable|in:SIM,NÃO',
            'funcionando'     => 'nullable|in:SIM,NÃO',
            'mais_informacoes'=> 'nullable|string|max:5000',
        ]);

        $material->update($request->only([
            'local_id', 'responsavel_id', 'em_uso', 'funcionando', 'mais_informacoes',
        ]));

        $selecaoIds = array_filter(array_map('intval', (array)$request->input('selecoes', [])));
        $material->selecoes()->sync($selecaoIds);

        return redirect()->route('material.show', $material)
            ->with('sucesso', 'Material atualizado com sucesso.');
    }

    public function uploadFoto(Request $request, Material $material)
    {
        $request->validate([
            'foto'     => 'required|image|max:10240',
            'tipo'     => 'required|in:material,local',
            'descricao'=> 'nullable|string|max:200',
        ]);

        $path = $request->file('foto')->store("fotos/{$material->id}", 'public');

        Foto::create([
            'material_id' => $material->id,
            'tipo'        => $request->tipo,
            'caminho'     => $path,
            'descricao'   => $request->descricao,
        ]);

        return back()->with('sucesso', 'Foto adicionada.');
    }

    public function deleteFoto(Foto $foto)
    {
        Storage::disk('public')->delete($foto->caminho);
        $foto->delete();
        return back()->with('sucesso', 'Foto removida.');
    }

    public function storeReparo(Request $request, Material $material)
    {
        $request->validate([
            'descricao'   => 'required|string',
            'data_inicio' => 'required|date',
            'observacoes' => 'nullable|string',
        ]);

        Reparo::create([
            'material_id'      => $material->id,
            'descricao'        => $request->descricao,
            'data_inicio'      => $request->data_inicio,
            'status'           => 'em_andamento',
            'setor_responsavel'=> session('setor_nome'),
            'observacoes'      => $request->observacoes,
        ]);

        $material->update(['situacao' => 'Em Reparo']);

        return back()->with('sucesso', 'Reparo registrado.');
    }

    public function concluirReparo(Reparo $reparo)
    {
        $reparo->update([
            'status'         => 'concluido',
            'data_conclusao' => now()->toDateString(),
        ]);

        $emAndamento = Reparo::where('material_id', $reparo->material_id)
            ->where('status', 'em_andamento')->count();

        if ($emAndamento === 0) {
            $reparo->material->update(['situacao' => 'Em Uso']);
        }

        return back()->with('sucesso', 'Reparo concluído.');
    }
}
