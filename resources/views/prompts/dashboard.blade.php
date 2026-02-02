<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prompts Dashboard - PromptVault</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
            border-bottom: 1px solid rgba(100, 116, 139, 0.2);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #e2e8f0;
        }

        .user-email {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid rgba(100, 116, 139, 0.2);
            padding-bottom: 0;
        }

        .nav-tab {
            padding: 12px 20px;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            top: 1px;
        }

        .nav-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .nav-tab:hover:not(.active) {
            color: #cbd5e1;
        }

        /* Main Content */
        .main-content {
            margin-top: 40px;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* Search & Filter */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(100, 116, 139, 0.3);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 14px;
        }

        .search-input::placeholder {
            color: #64748b;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(30, 41, 59, 1);
        }

        .filter-select {
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(100, 116, 139, 0.3);
            border-radius: 8px;
            color: #e2e8f0;
            cursor: pointer;
            font-size: 14px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-create {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Prompts Grid */
        .prompts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .prompt-card {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.5) 0%, rgba(30, 41, 59, 0.8) 100%);
            border: 1px solid rgba(100, 116, 139, 0.2);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
        }

        .prompt-card:hover {
            border-color: #667eea;
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
        }

        .prompt-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 10px;
        }

        .prompt-title {
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
            margin: 0;
            flex: 1;
        }

        .prompt-badge {
            display: inline-block;
            padding: 4px 8px;
            background: rgba(102, 126, 234, 0.2);
            color: #93c5fd;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .prompt-owner {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0;
        }

        .prompt-description {
            font-size: 13px;
            color: #cbd5e1;
            line-height: 1.5;
            margin: 8px 0;
        }

        .prompt-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: #94a3b8;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid rgba(100, 116, 139, 0.1);
        }

        .prompt-category {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
            color: #93c5fd;
            font-size: 11px;
        }

        .prompt-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-small {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn-view {
            background: rgba(102, 126, 234, 0.2);
            color: #93c5fd;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .btn-view:hover {
            background: rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }

        .btn-edit {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .btn-edit:hover {
            background: rgba(34, 197, 94, 0.3);
            border-color: #22c55e;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.3);
            border-color: #ef4444;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 10px;
        }

        .empty-text {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        /* Pagination - Updated */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pagination > * {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagination .pagination-list {
            display: flex;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination .page-item {
            display: inline-block;
        }

        .pagination .page-link,
        .pagination .page-item span:not(.sr-only) {
            padding: 8px 12px;
            border: 1px solid rgba(100, 116, 139, 0.3);
            border-radius: 6px;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            min-width: 40px;
            text-align: center;
            font-size: 14px;
        }

        .pagination .page-link:hover {
            border-color: #667eea;
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            color: #64748b;
        }

        .pagination .page-item.disabled .page-link:hover {
            border-color: rgba(100, 116, 139, 0.3);
            color: #64748b;
            background: transparent;
        }

        /* Pagination info */
        .pagination-info {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            margin-top: 10px;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                align-items: start;
            }

            .user-menu {
                width: 100%;
                justify-content: space-between;
            }

            .nav-tabs {
                overflow-x: auto;
            }

            .prompts-grid {
                grid-template-columns: 1fr;
            }

            .search-bar {
                flex-direction: column;
            }

            .search-input {
                min-width: 100%;
            }

            .pagination {
                flex-direction: column;
                gap: 15px;
            }

            .pagination > * {
                width: 100%;
                justify-content: center;
            }

            .pagination .pagination-list {
                order: 2;
            }

            .pagination-info {
                order: 1;
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span>🔹</span> PromptVault
                </div>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-email">{{ Auth::user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="logout-btn">Cerrar Sesión</button>
                    </form>
                </div>
            </div>

            <!-- Tabs -->
            <div class="nav-tabs">
                <button class="nav-tab active" onclick="switchTab('prompts-sistema')">
                    📚 Prompts del Sistema
                </button>
                <button class="nav-tab" onclick="switchTab('mis-prompts')">
                    ✏️ Mis Prompts
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <!-- Section: Prompts del Sistema -->
            <div id="prompts-sistema" class="section active">
                <h2 style="margin-bottom: 30px; font-size: 28px; color: #e2e8f0;">
                    📚 Explorar Prompts del Sistema
                </h2>

                <!-- Search & Filter -->
                <form method="GET" action="{{ route('prompts.dashboard') }}" class="search-bar">
                    <input 
                        type="text" 
                        name="q" 
                        class="search-input" 
                        placeholder="Buscar prompts..."
                        value="{{ $q }}"
                    >
                    <select name="categoria_id" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Prompts Grid -->
                @if($promptsDelSistema->count() > 0)
                    <div class="prompts-grid">
                        @foreach($promptsDelSistema as $prompt)
                            <div class="prompt-card">
                                <div class="prompt-header">
                                    <h3 class="prompt-title">{{ $prompt->titulo }}</h3>
                                    @if($prompt->es_favorito)
                                        <span class="prompt-badge">⭐ Favorito</span>
                                    @endif
                                </div>
                                
                                <div class="prompt-owner">
                                    Creado por: <strong>{{ $prompt->user->name }}</strong>
                                </div>

                                <p class="prompt-description">
                                    {{ Str::limit($prompt->contenido, 100) }}
                                </p>

                                <div class="prompt-meta">
                                    @if($prompt->categoria)
                                        <span class="prompt-category">📁 {{ $prompt->categoria->nombre }}</span>
                                    @endif
                                    <span>📅 {{ $prompt->created_at->format('d/m/Y') }}</span>
                                </div>

                                <div class="prompt-actions">
                                    <a href="{{ route('prompts.show', $prompt->id) }}" class="btn-small btn-view">
                                        👁 Ver
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($promptsDelSistema->hasPages())
                    <div class="pagination">
                        <!-- Pagination Info -->
                        <div class="pagination-info">
                            Mostrando {{ $promptsDelSistema->firstItem() }} a {{ $promptsDelSistema->lastItem() }} de {{ $promptsDelSistema->total() }} resultados
                        </div>
                        
                        <!-- Pagination Links -->
                        <div>
                            <ul class="pagination-list">
                                {{-- Previous Page Link --}}
                                @if($promptsDelSistema->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo; Anterior</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $promptsDelSistema->previousPageUrl() }}" rel="prev">&laquo; Anterior</a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach(range(1, $promptsDelSistema->lastPage()) as $page)
                                    @if($page == $promptsDelSistema->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $promptsDelSistema->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if($promptsDelSistema->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $promptsDelSistema->nextPageUrl() }}" rel="next">Siguiente &raquo;</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">Siguiente &raquo;</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-title">No hay prompts disponibles</div>
                        <div class="empty-text">No se encontraron prompts públicos en el sistema.</div>
                    </div>
                @endif
            </div>

            <!-- Section: Mis Prompts -->
            <div id="mis-prompts" class="section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h2 style="font-size: 28px; color: #e2e8f0; margin: 0;">
                        ✏️ Mis Prompts
                    </h2>
                    <a href="{{ route('prompts.create') }}" class="btn-create">
                        + Crear Nuevo Prompt
                    </a>
                </div>

                @if($promptsPropios->count() > 0)
                    <div class="prompts-grid">
                        @foreach($promptsPropios as $prompt)
                            <div class="prompt-card">
                                <div class="prompt-header">
                                    <h3 class="prompt-title">{{ $prompt->titulo }}</h3>
                                    @if($prompt->es_favorito)
                                        <span class="prompt-badge">⭐ Favorito</span>
                                    @endif
                                </div>

                                <p class="prompt-description">
                                    {{ Str::limit($prompt->contenido, 100) }}
                                </p>

                                <div class="prompt-meta">
                                    @if($prompt->categoria)
                                        <span class="prompt-category">📁 {{ $prompt->categoria->nombre }}</span>
                                    @endif
                                    <span>📅 {{ $prompt->created_at->format('d/m/Y') }}</span>
                                    @if($prompt->es_publico)
                                        <span style="background: rgba(34, 197, 94, 0.2); padding: 4px 8px; border-radius: 4px; color: #86efac; font-size: 11px;">🔓 Público</span>
                                    @else
                                        <span style="background: rgba(239, 68, 68, 0.2); padding: 4px 8px; border-radius: 4px; color: #fca5a5; font-size: 11px;">🔒 Privado</span>
                                    @endif
                                </div>

                                <div class="prompt-actions">
                                    <a href="{{ route('prompts.show', $prompt->id) }}" class="btn-small btn-view">
                                        👁 Ver
                                    </a>
                                    <a href="{{ route('prompts.edit', $prompt->id) }}" class="btn-small btn-edit">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('prompts.destroy', $prompt->id) }}" method="POST" style="flex: 1;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-small btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este prompt?')">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <div class="empty-title">No tienes prompts creados</div>
                        <div class="empty-text">Crea tu primer prompt para comenzar a gestionar tus ideas.</div>
                        <a href="{{ route('prompts.create') }}" class="btn-create" style="display: inline-block; margin-top: 20px;">
                            + Crear Nuevo Prompt
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            // Ocultar todos los tabs
            document.querySelectorAll('.section').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.nav-tab').forEach(el => {
                el.classList.remove('active');
            });

            // Mostrar el tab seleccionado
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

    <!-- AI Chat Widget -->
    @include('components.ai-widget-injection')
</body>
</html>