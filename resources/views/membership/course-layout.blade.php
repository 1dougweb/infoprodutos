<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Designerflix') - Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: {{ \App\Models\Setting::get('primary_color', '#007bff') }};
            --secondary-blue: {{ \App\Models\Setting::get('secondary_color', '#0056b3') }};
            --dark-bg: {{ \App\Models\Setting::get('background_color', '#0f0f0f') }};
            --card-bg: {{ \App\Models\Setting::get('card_background', '#2a2a2a') }};
            --text-light: {{ \App\Models\Setting::get('text_light', '#ffffff') }};
            --text-muted: {{ \App\Models\Setting::get('text_muted', '#b3b3b3') }};
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Header do Curso */
        .course-header-bar {
            background-color: var(--card-bg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .course-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 auto;
        }

        .course-back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .course-back-button:hover {
            color: var(--primary-blue);
            text-decoration: none;
        }

        .course-header-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--text-light);
        }

        .course-user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .course-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-light);
        }

        .course-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }

        .course-user-name {
            font-size: 14px;
            font-weight: 500;
        }

        .course-user-info {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .course-user-info:hover {
            opacity: 0.8;
        }

        .user-menu-toggle {
            font-size: 12px;
            color: var(--text-muted);
            transition: transform 0.3s ease;
        }

        .user-menu-toggle.active {
            transform: rotate(180deg);
        }

        .user-context-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            min-width: 200px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-context-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .menu-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-light);
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .menu-item i {
            font-size: 14px;
            color: var(--text-muted);
        }

        .menu-item a {
            color: var(--text-light);
            text-decoration: none;
            flex: 1;
        }

        .menu-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 8px 0;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 14px;
            flex: 1;
            text-align: left;
            padding: 0;
        }

        .logout-btn:hover {
            color: #dc3545;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-blue);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-blue);
        }

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-blue) rgba(255, 255, 255, 0.1);
        }

        /* Conteúdo principal */
        .course-main-content {
            min-height: calc(100vh - 70px);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .course-header-content {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .course-user-menu {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header do Curso -->
    <div class="course-header-bar">
        <div class="course-header-content">
            <a href="{{ route('membership.index') }}" class="course-back-button">
                <i class="bi bi-arrow-left"></i>
                Voltar ao Dashboard
            </a>
            
            <div class="course-header-title">
                @yield('title', 'Curso')
            </div>
            
            <div class="course-user-menu">
                <div class="course-user-info" onclick="toggleUserMenu()">
                    <div class="course-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="course-user-name">
                        {{ auth()->user()->name }}
                    </div>
                    <i class="bi bi-chevron-down user-menu-toggle"></i>
                </div>
                
                <div class="user-context-menu" id="userContextMenu">
                    <div class="menu-item">
                        <i class="bi bi-person"></i>
                        <a href="{{ route('membership.profile') }}">Meu Perfil</a>
                    </div>
                    <div class="menu-item">
                        <i class="bi bi-house"></i>
                        <a href="{{ route('membership.index') }}">Dashboard</a>
                    </div>
                    <div class="menu-divider"></div>
                    <div class="menu-item">
                        <i class="bi bi-box-arrow-right"></i>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="logout-btn">Sair</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="course-main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script>
    // Função para alternar o menu de contexto do usuário
    function toggleUserMenu() {
        const menu = document.getElementById('userContextMenu');
        const toggle = document.querySelector('.user-menu-toggle');
        
        menu.classList.toggle('active');
        toggle.classList.toggle('active');
    }

    // Fechar menu quando clicar fora
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.course-user-info');
        const contextMenu = document.getElementById('userContextMenu');
        
        if (!userMenu.contains(event.target) && !contextMenu.contains(event.target)) {
            contextMenu.classList.remove('active');
            document.querySelector('.user-menu-toggle').classList.remove('active');
        }
    });
    </script>
</body>
</html> 