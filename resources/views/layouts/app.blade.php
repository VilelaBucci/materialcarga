<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d47a1">
    <title>VilSystem - Material de Carga</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --vs-blue: #0d47a1; --vs-blue-light: #1565c0; }
        body { background: #f0f4f8; font-size: 0.92rem; overflow-x: hidden; }
        .navbar-brand img { height: 36px; }
        .sidebar { min-height: calc(100vh - 56px); background: var(--vs-blue); }
        .sidebar .nav-link { color: rgba(255,255,255,.75); padding: .5rem 1rem; border-radius: 6px; margin: 2px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        .sidebar .nav-link i { width: 20px; }
        .sidebar-section { font-size: .7rem; color: rgba(255,255,255,.4); text-transform: uppercase; padding: .5rem 1rem; margin-top: .5rem; letter-spacing: .05rem; }
        .main-content { background: #f0f4f8; min-height: calc(100vh - 56px); overflow-x: hidden; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; border-radius: 12px 12px 0 0 !important; font-weight: 600; }
        .table > :not(caption) > * > * { padding: .55rem .75rem; }
        .badge { font-weight: 500; }
        .situacao-A { background: #e8f5e9; color: #388e3c; }
        .situacao-R { background: #ffebee; color: #c62828; }
        .situacao-P { background: #fff8e1; color: #f57f17; }
        .situacao-D { background: #eceff1; color: #546e7a; }
        @media (max-width: 767px) {
            .sidebar { display: none !important; }
            .main-content { padding: .5rem .5rem 72px !important; }
        }
        .setor-badge { background: rgba(255,255,255,.2); border-radius: 6px; padding: 3px 10px; font-size: .8rem; color: #fff; }
        .field-label { font-size: .68rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: .03rem; margin-bottom: 2px; }
        .w-md-auto { width: auto !important; }
        .nav-mobile { display: none; }
        @media (max-width: 767px) { .nav-mobile { display: flex; } }
    </style>
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-dark py-1" style="background: var(--vs-blue);">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="bi bi-box-seam fs-5"></i>
            <span class="fw-bold">VilSystem</span>
            <small class="d-none d-md-inline opacity-75">Material de Carga</small>
        </a>

        <div class="d-flex align-items-center gap-2">
            <span class="setor-badge d-none d-sm-inline">
                <i class="bi bi-building"></i>
                @if(session('unidade_nome') && session('unidade_nome') !== session('setor_nome'))
                    {{ session('unidade_nome') }} /
                @endif
                {{ session('setor_sigla') ?? session('setor_nome') }}
                @if(session('is_admin')) <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">ADMIN</span> @endif
                @if(!session('pode_editar')) <span class="badge bg-secondary ms-1" style="font-size:.65rem">LEITURA</span> @endif
            </span>

            {{-- Mobile menu --}}
            <button class="btn btn-sm btn-outline-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMobile">
                <i class="bi bi-list"></i>
            </button>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
</nav>

<div class="d-flex">
    {{-- Sidebar Desktop --}}
    <div class="sidebar d-none d-md-block" style="width:220px;flex-shrink:0;">
        <div class="pt-3 pb-2">
            <div class="sidebar-section">Menu</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('material.index') }}" class="nav-link {{ request()->routeIs('material.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i> Material
            </a>

            @if(session('pode_editar'))
            <div class="sidebar-section">Cadastros</div>
            <a href="{{ route('locais.index') }}" class="nav-link {{ request()->routeIs('locais.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Locais
            </a>
            <a href="{{ route('responsaveis.index') }}" class="nav-link {{ request()->routeIs('responsaveis.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Responsáveis
            </a>
            <a href="{{ route('selecoes.index') }}" class="nav-link {{ request()->routeIs('selecoes.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Grupos
            </a>
            @endif

            @if(session('is_admin'))
            <div class="sidebar-section">Administração</div>
            <a href="{{ route('admin.setores') }}" class="nav-link {{ request()->routeIs('admin.setores') ? 'active' : '' }}">
                <i class="bi bi-key"></i> Senhas dos Setores
            </a>
            @if(session('is_master'))
            <a href="{{ route('master') }}" class="nav-link {{ request()->routeIs('master') ? 'active' : '' }}" style="color:#ff8a80">
                <i class="bi bi-shield-fill-check"></i> Senha Master
            </a>
            @endif
            <a href="{{ route('admin.nova-unidade') }}" class="nav-link">
                <i class="bi bi-plus-circle"></i> Nova Unidade
            </a>
            <a href="{{ route('admin.importar') }}" class="nav-link {{ request()->routeIs('admin.importar*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
            </a>
            <form action="{{ route('admin.global') }}" method="POST" class="px-2 mt-1">
                @csrf
                @if(session('ver_todos'))
                <button class="btn btn-sm btn-warning w-100">
                    <i class="bi bi-building"></i> Voltar ao Setor
                </button>
                @else
                <button class="btn btn-sm btn-outline-warning w-100">
                    <i class="bi bi-globe"></i> Ver Todos os Setores
                </button>
                @endif
            </form>
            @endif

            <div class="sidebar-section mt-3">Sessão</div>
            <div class="px-3 py-1">
                @if(session('unidade_nome') && session('unidade_nome') !== session('setor_nome'))
                    <small class="text-white-50 d-block">{{ Str::limit(session('unidade_nome'), 28) }}</small>
                @endif
                <small class="text-white-50">{{ Str::limit(session('setor_nome'), 30) }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="px-3 mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right"></i> Sair</button>
            </form>
        </div>
    </div>

    {{-- Conteúdo principal --}}
    <div class="main-content flex-grow-1 p-3">
        @if(session('sucesso'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('sucesso') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('erro'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('erro') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e) <div><i class="bi bi-x-circle"></i> {{ $e }}</div> @endforeach
            </div>
        @endif

        @if(session('is_admin') && session('ver_todos'))
        <div class="alert alert-warning py-2 mb-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-globe"></i> <strong>Modo Global</strong> — visualizando todos os setores</span>
            <form action="{{ route('admin.global') }}" method="POST" class="mb-0">
                @csrf
                <button class="btn btn-sm btn-warning"><i class="bi bi-building"></i> Voltar ao Setor</button>
            </form>
        </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Offcanvas Mobile --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="menuMobile" style="background:var(--vs-blue);max-width:250px;">
    <div class="offcanvas-header">
        <span class="text-white fw-bold"><i class="bi bi-box-seam"></i> VilSystem</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('material.index') }}" class="nav-link {{ request()->routeIs('material.*') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> Material
        </a>
        @if(session('pode_editar'))
        <a href="{{ route('locais.index') }}" class="nav-link">
            <i class="bi bi-geo-alt"></i> Locais
        </a>
        <a href="{{ route('responsaveis.index') }}" class="nav-link">
            <i class="bi bi-person-badge"></i> Responsáveis
        </a>
        <a href="{{ route('selecoes.index') }}" class="nav-link">
            <i class="bi bi-tags"></i> Grupos
        </a>
        @endif
        @if(session('is_admin'))
        <a href="{{ route('admin.setores') }}" class="nav-link">
            <i class="bi bi-key"></i> Senhas dos Setores
        </a>
        <a href="{{ route('admin.nova-unidade') }}" class="nav-link">
            <i class="bi bi-plus-circle"></i> Nova Unidade
        </a>
        <a href="{{ route('admin.importar') }}" class="nav-link">
            <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
        </a>
        @if(session('is_master'))
        <a href="{{ route('master') }}" class="nav-link" style="color:#ff8a80">
            <i class="bi bi-shield-fill-check"></i> Senha Master
        </a>
        @endif
        <div class="px-3 mt-1">
            <form action="{{ route('admin.global') }}" method="POST">
                @csrf
                @if(session('ver_todos'))
                <button class="btn btn-sm btn-warning w-100"><i class="bi bi-building"></i> Voltar ao Setor</button>
                @else
                <button class="btn btn-sm btn-outline-warning w-100"><i class="bi bi-globe"></i> Ver Todos os Setores</button>
                @endif
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Navbar Mobile Bottom --}}
<nav class="fixed-bottom d-md-none navbar" style="background:var(--vs-blue);padding:.3rem 0;">
    <div class="container-fluid justify-content-around">
        <a href="{{ route('dashboard') }}" class="text-center text-white text-decoration-none" style="font-size:.65rem">
            <i class="bi bi-speedometer2 d-block fs-5"></i>Dashboard
        </a>
        <a href="{{ route('material.index') }}" class="text-center text-white text-decoration-none" style="font-size:.65rem">
            <i class="bi bi-boxes d-block fs-5"></i>Material
        </a>
        @if(session('pode_editar'))
        <a href="{{ route('locais.index') }}" class="text-center text-white text-decoration-none" style="font-size:.65rem">
            <i class="bi bi-geo-alt d-block fs-5"></i>Locais
        </a>
        <a href="{{ route('responsaveis.index') }}" class="text-center text-white text-decoration-none" style="font-size:.65rem">
            <i class="bi bi-person-badge d-block fs-5"></i>Responsáveis
        </a>
        <a href="{{ route('selecoes.index') }}" class="text-center text-white text-decoration-none" style="font-size:.65rem">
            <i class="bi bi-tags d-block fs-5"></i>Grupos
        </a>
        @endif
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
