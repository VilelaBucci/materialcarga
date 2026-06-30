<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\ResponsavelController;
use App\Http\Controllers\SelecaoController;
use App\Models\Setor;
use App\Models\Unidade;
use Illuminate\Support\Facades\Route;

// Rota pública AJAX: setores de uma unidade (sem auth — apenas nomes/ids, sem dados sensíveis)
Route::get('/api/unidade/{id}/setores', function ($id) {
    $unidade = Unidade::find($id);
    if (!$unidade) return response()->json([]);
    return response()->json(
        $unidade->setores()->select('id', 'nome', 'sigla')->get()
    );
});

// Nova unidade: acessível sem login (mas protegida por senha_adm dentro do controller)
Route::get('/nova-unidade',  [AdminController::class, 'novaUnidade'])->name('admin.nova-unidade');
Route::post('/nova-unidade', [AdminController::class, 'criarUnidade'])->name('admin.nova-unidade.post');
Route::post('/validar-csv',  [AdminController::class, 'validarCsv'])->name('admin.validar-csv');

// Auth
Route::get('/',       [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'autenticar'])->name('login.post');
Route::post('/soLeitura', [AuthController::class, 'soLeitura'])->name('login.soLeitura');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Área autenticada
Route::middleware('setor')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/trocar-unidade', [AuthController::class, 'trocarUnidade'])->name('unidade.trocar');
    Route::post('/admin/global', [AuthController::class, 'toggleGlobal'])->name('admin.global');
    Route::get('/admin/setores', [AdminController::class, 'setores'])->name('admin.setores');
    Route::put('/admin/setores/{setor}/senha', [AdminController::class, 'atualizarSenha'])->name('admin.setores.senha');
    Route::put('/admin/senha-adm', [AdminController::class, 'atualizarSenhaAdm'])->name('admin.senha.adm');
    Route::get('/master',  [AdminController::class, 'masterForm'])->name('master');
    Route::post('/master', [AdminController::class, 'masterAtualizar'])->name('master.atualizar');
    Route::get('/admin/importar', [ImportacaoController::class, 'formulario'])->name('admin.importar');
    Route::post('/admin/importar', [ImportacaoController::class, 'importar'])->name('admin.importar.post');

    // Material - visualização e PDF
    Route::get('/material',            [MaterialController::class, 'index'])->name('material.index');
    Route::get('/material-pdf',        [MaterialController::class, 'pdf'])->name('material.pdf');
    Route::get('/material/{material}', [MaterialController::class, 'show'])->name('material.show');

    // Material - edição (requer pode_editar)
    Route::middleware('pode_editar')->group(function () {
        Route::get('/material/{material}/editar', [MaterialController::class, 'edit'])->name('material.edit');
        Route::put('/material/{material}',        [MaterialController::class, 'update'])->name('material.update');

        Route::post('/material/{material}/foto',  [MaterialController::class, 'uploadFoto'])->name('material.foto.upload');
        Route::delete('/foto/{foto}',             [MaterialController::class, 'deleteFoto'])->name('material.foto.delete');

        Route::post('/material/{material}/reparo', [MaterialController::class, 'storeReparo'])->name('material.reparo.store');
        Route::post('/reparo/{reparo}/concluir',   [MaterialController::class, 'concluirReparo'])->name('material.reparo.concluir');

        Route::resource('/locais',       LocalController::class)->except(['create', 'edit', 'show'])->parameters(['locais' => 'local']);
        Route::resource('/responsaveis', ResponsavelController::class)->except(['create', 'edit', 'show'])->parameters(['responsaveis' => 'responsavel']);
        Route::resource('/selecoes',     SelecaoController::class)->except(['create', 'edit', 'show'])->parameters(['selecoes' => 'selecao']);
    });
});
