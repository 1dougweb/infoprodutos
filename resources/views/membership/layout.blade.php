<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Designerflix') - Área de Membros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ url('/css/app.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-blue: {{ \App\Models\Setting::get('primary_color', '#007bff') }};
            --secondary-blue: {{ \App\Models\Setting::get('secondary_color', '#0056b3') }};
            --dark-bg: {{ \App\Models\Setting::get('background_color', '#0f0f0f') }};
            --sidebar-bg: #1a1a1a;
            --card-bg: {{ \App\Models\Setting::get('card_background', '#2a2a2a') }};
            --text-light: {{ \App\Models\Setting::get('text_light', '#ffffff') }};
            --text-muted: {{ \App\Models\Setting::get('text_muted', '#b3b3b3') }};
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            z-index: 1000;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logo img {
            max-height: 50px;
            max-width: 200px;
            width: auto;
            object-fit: contain;
        }

        .logo-text-only {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 30px;
            text-align: center;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 8px 12px;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--primary-blue);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: white;
        }

        .nav-section-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 10px 0;
            padding: 0 12px;
        }

        /* Perfil do usuário no rodapé */
        .user-profile-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-profile-card {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-profile-card:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-blue);
        }

        .user-profile-card.expanded {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-blue);
        }

        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-blue), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-profile-toggle {
            color: var(--text-muted);
            transition: transform 0.3s ease;
        }

        .user-profile-card.expanded .user-profile-toggle {
            transform: rotate(180deg);
        }

        .user-profile-options {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            margin-top: 0;
        }

        .user-profile-card.expanded .user-profile-options {
            max-height: 200px;
            margin-top: 15px;
        }

        .user-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        .user-option:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--primary-blue);
        }

        .user-option.logout {
            color: #dc3545;
        }

        .user-option.logout:hover {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }

        .card-header {
            background-color: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header h5,
        .card-header h6 {
            color: var(--text-light) !important;
            margin: 0;
        }

        .card-header i {
            color: var(--primary-blue) !important;
            margin-right: 8px;
        }

        .card-title {
            color: var(--text-light);
            margin: 0;
        }

        .card-title i {
            color: var(--primary-blue);
            margin-right: 8px;
        }

        /* Ícones dentro de botões devem herdar a cor do texto do botão */
        .btn i {
            color: inherit !important;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-blue), var(--secondary-blue));
            border: none;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--secondary-blue), #004085);
            filter: brightness(0.9);
        }

        /* Presets de cores */
        .preset-btn {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .preset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .preset-btn.btn-primary {
            border-color: var(--primary-blue);
            background-color: rgba(0, 123, 255, 0.1);
        }

        .preset-color {
            margin-bottom: 5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Botão group */
        .btn-group .btn {
            margin-right: 2px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Modais */
        .modal-content.bg-dark {
            background-color: var(--card-bg) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: #f8d7da;
        }

        .alert-danger i {
            color: #dc3545;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }
        
        /* Remover seta do dropdown */
        .dropdown-toggle::after {
            display: none !important;
        }
        
        /* Garantir que apenas o ícone de 3 dots seja exibido */
        .bi-three-dots-vertical {
            font-size: 1.2rem;
        }
        
        /* Controlar altura dos charts */
        .chart-area {
            position: relative;
            height: 20rem !important;
            width: 100%;
        }
        
        .chart-bar {
            position: relative;
            height: 15rem !important;
            width: 100%;
        }
        
        /* Dropdown menus dark */
        .dropdown-menu {
            background-color: var(--card-bg) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5) !important;
        }
        
        .dropdown-item {
            color: var(--text-light) !important;
            padding: 0.5rem 1rem !important;
        }
        
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-light) !important;
        }
        
        .dropdown-header {
            color: var(--text-muted) !important;
            font-weight: 600 !important;
            padding: 0.5rem 1rem !important;
        }
        
        .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1) !important;
            margin: 0.5rem 0 !important;
        }
        
        /* Modal text colors */
        .modal-header h5 {
            color: var(--text-light) !important;
        }
        
        .modal-body {
            color: var(--text-light) !important;
        }
        
        .modal-body strong {
            color: var(--text-light) !important;
        }
        
        .modal-body p {
            color: var(--text-light) !important;
        }
        
        .modal-body .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        .modal-body .list-unstyled li {
            color: var(--text-light) !important;
        }
        
        .modal-body h6 {
            color: var(--text-light) !important;
        }
        
        .modal-body .text-primary {
            color: var(--primary-blue) !important;
        }
        
        .modal-body .text-success {
            color: #28a745 !important;
        }
        
        /* Centralizar modais */
        .centered-modal .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }
        
        @media (min-width: 576px) {
            .centered-modal .modal-dialog {
                min-height: calc(100% - 3.5rem);
            }
        }
        
        /* Campos select dark mode */
        .form-select {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: var(--text-light) !important;
        }
        
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-color: var(--primary-blue) !important;
            color: var(--text-light) !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Opções do select dark mode */
        .form-select option {
            background-color: var(--card-bg) !important;
            color: var(--text-light) !important;
        }
        
        /* Dropdown do select dark mode */
        .form-select:not([size]):not([multiple]) {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
        }
        
        /* Forçar dark mode em todos os selects */
        select.form-select,
        select.form-control {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: var(--text-light) !important;
        }
        
        select.form-select:focus,
        select.form-control:focus {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-color: var(--primary-blue) !important;
            color: var(--text-light) !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Opções do select sempre dark */
        select option {
            background-color: var(--card-bg) !important;
            color: var(--text-light) !important;
        }
        
        /* Hover nas opções */
        select option:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        /* Forçar background dos botões dentro de células de tabela */
        .table td .btn-info {
            background-color: #0dcaf0 !important;
            border-color: #0dcaf0 !important;
            color: #000 !important;
        }
        
        .table td .btn-warning {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .table td .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        
        .table td .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #fff !important;
        }
        
        .table td .btn-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        
        .table td .btn-secondary {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        
        .table td .btn-light {
            background-color: #f8f9fa !important;
            border-color: #f8f9fa !important;
            color: #000 !important;
        }
        
        .table td .btn-dark {
            background-color: #212529 !important;
            border-color: #212529 !important;
            color: #fff !important;
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .text-primary {
            color: var(--primary-blue) !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        /* Input de cor */
        .form-control-color {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .form-control-color:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Form text */
        .form-text {
            color: var(--text-muted) !important;
        }

        /* Text muted in dark cards */
        .card .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .card p.text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Strong elements in dark cards */
        .card strong {
            color: var(--text-light) !important;
        }

        /* Labels */
        .form-label {
            color: var(--text-light) !important;
            font-weight: 500;
        }

        /* Placeholder */
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Tabs */
        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-tabs .nav-link {
            color: var(--text-muted);
            border: none;
            background: none;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
        }

        .nav-tabs .nav-link:hover {
            color: var(--text-light);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-blue);
            background-color: rgba(255, 255, 255, 0.1);
            border-bottom: 2px solid var(--primary-blue);
        }

        /* Tabelas Padrão */
        .table {
            color: var(--text-light) !important;
            background-color: var(--card-bg) !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }

        .table th {
            border-color: rgba(255, 255, 255, 0.1) !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-light) !important;
            font-weight: 600 !important;
            padding: 12px 15px !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1) !important;
        }

        .table td {
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-light) !important;
            padding: 12px 15px !important;
            vertical-align: middle !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
            transition: background-color 0.2s ease !important;
        }

        .table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        .table tbody tr:last-child {
            border-bottom: none !important;
        }

        /* Forçar fundo escuro em todas as tabelas */
        .table, .table-striped, .table-bordered {
            background-color: var(--card-bg) !important;
        }

        .text-muted {
            --bs-text-opacity: 0;
            color: transparent!important;
        }

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .table-striped > tbody > tr:nth-of-type(odd):hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        /* Forçar cores escuras em todas as tabelas */
        .card .table,
        .card-body .table,
        .table-responsive .table {
            background-color: var(--card-bg) !important;
            color: var(--text-light) !important;
        }

        .card .table th,
        .card-body .table th,
        .table-responsive .table th {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-light) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .card .table td,
        .card-body .table td,
        .table-responsive .table td {
            background-color: transparent !important;
            color: var(--text-light) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Sobrescrever qualquer fundo branco */
        .table {
            background-color: var(--card-bg) !important;
        }

        /* Excluir botões da regra de fundo da tabela */
        .table .btn {
            background-color: initial !important;
        }

        .table th {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .table td {
            background-color: transparent !important;
        }

        /* Ações da tabela */
        .table-actions {
            display: flex;
            gap: 5px;
            justify-content: center;
            background-color: transparent!important;
        }

        /* Formulários Dark Mode */
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border-radius: 8px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-blue);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control:disabled {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            opacity: 0.7;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border-radius: 8px;
        }

        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-blue);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .form-check-label {
            color: var(--text-light);
        }

        /* Upload de Arquivos */
        .file-upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--primary-blue);
            background-color: rgba(0, 123, 255, 0.1);
        }

        .file-upload-area.dragover {
            border-color: var(--primary-blue);
            background-color: rgba(0, 123, 255, 0.2);
        }

        .file-upload-icon {
            font-size: 48px;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .file-upload-text {
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        small {
            background-color: transparent!important;
        }

        .file-upload-info {
            font-size: 12px;
            color: var(--text-muted);
        }

        .current-file {
            background-color: rgba(0, 123, 255, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .current-file-name {
            font-weight: bold;
            color: var(--primary-blue);
        }

        .current-file-size {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Alertas */
        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .alert-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .alert-info {
            background-color: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }

        /* Modal Dark */
        .modal-content {
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-close {
            filter: invert(1);
        }

        /* Paginação */
        .pagination .page-link {
            background-color: var(--card-bg);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .pagination .page-link:hover {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        /* Badges */
        .badge {
            font-weight: 500;
        }

        .badge.bg-success {
            background-color: #28a745 !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        .badge.bg-primary {
            background-color: var(--primary-blue) !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        body {
        --sb-track-color: #232E33;
        --sb-thumb-color: var(--primary-blue);
        --sb-size: 5px;
        }

        body::-webkit-scrollbar {
        width: var(--sb-size)
        }

        body::-webkit-scrollbar-track {
        background: var(--sb-track-color);
        border-radius: 3px;
        }

        body::-webkit-scrollbar-thumb {
        background: var(--sb-thumb-color);
        border-radius: 3px;
        
        }

        @supports not selector(::-webkit-scrollbar) {
        body {
            scrollbar-color: var(--sb-thumb-color)
                            var(--sb-track-color);
        }
        }

        /* Scrollbar para o sidebar */
        .sidebar::-webkit-scrollbar {
            width: var(--sb-size)
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--sb-track-color);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--sb-thumb-color);
            border-radius: 3px;
        }

        @supports not selector(::-webkit-scrollbar) {
        .sidebar {
            scrollbar-color: var(--sb-thumb-color)
                            var(--sb-track-color);
        }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo no topo -->
        @php
            $logoPath = \App\Models\Setting::get('logo_path');
            $siteName = \App\Models\Setting::get('site_name', 'Painel de Controle');
        @endphp
        
        @if($logoPath)
            <div class="logo">
                <img src="{{ url($logoPath) }}" alt="{{ $siteName }}">
            </div>
        @else
            <div class="logo-text-only">
                {{ $siteName }}
            </div>
        @endif

        <!-- Menu de navegação -->
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('membership.index') }}" class="nav-link {{ request()->routeIs('membership.index') ? 'active' : '' }}">
                    <i class="bi bi-house-fill"></i>
                    Início
                </a>
            </li>
            
            <!-- Itens para todos os usuários -->
            <li class="nav-item">
                <a href="{{ route('membership.profile') }}" class="nav-link {{ request()->routeIs('membership.profile*') ? 'active' : '' }}">
                    <i class="bi bi-person-fill"></i>
                    Minha Conta
                </a>
            </li>
            
            <!-- Seção de cursos comprados -->
            @php
                $purchasedProducts = auth()->user()->purchases()->with('digitalProduct')->get();
            @endphp
            @if($purchasedProducts->count() > 0)
                <li class="nav-item">
                    <div class="nav-section-title">Meus Cursos</div>
                    @foreach($purchasedProducts->take(3) as $purchase)
                        <li class="nav-item">
                            <a href="{{ route('membership.course', $purchase->digitalProduct->id) }}" class="nav-link {{ request()->routeIs('membership.course') && request()->route('id') == $purchase->digitalProduct->id ? 'active' : '' }}">
                                <i class="bi bi-play-circle"></i>
                                {{ $purchase->digitalProduct->title }}
                            </a>
                        </li>
                    @endforeach
                    @if($purchasedProducts->count() > 3)
                        <li class="nav-item">
                            <a href="{{ route('membership.index') }}" class="nav-link">
                                <i class="bi bi-arrow-right"></i>
                                Ver todos os cursos
                            </a>
                        </li>
                    @endif
                </li>
            @endif
            
            <!-- Itens apenas para admins -->
            @if(auth()->user()->canViewDashboard())
            <li class="nav-item">
                <div class="nav-section-title">Administração</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    Painel Admin
                </a>
            </li>
            @endif
            @if(auth()->user()->canManageProducts())
            <li class="nav-item">
                <a href="{{ route('admin.products') }}" class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    Gerenciar Produtos
                </a>
            </li>
            @endif

            @if(auth()->user()->canViewDashboard())
            <li class="nav-item">
                <a href="{{ route('admin.banners') }}" class="nav-link {{ request()->routeIs('admin.banners*') ? 'active' : '' }}">
                    <i class="bi bi-images"></i>
                    Gerenciar Banners
                </a>
            </li>
            @endif

            @if(auth()->user()->canViewOrders())
            <li class="nav-item">
                <a href="{{ route('admin.orders') }}" class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i>
                    Ver Pedidos
                </a>
            </li>
            @endif
            @if(auth()->user()->canManageUsers())
            <li class="nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    Gerenciar Usuários
                </a>
            </li>
            @endif
            @if(auth()->user()->canViewDashboard())
            <li class="nav-item">
                <a href="{{ route('admin.online-users') }}" class="nav-link {{ request()->routeIs('admin.online-users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    Usuários Online
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    Configurações
                </a>
            </li>
            @endif
        </ul>

        <!-- Perfil do usuário no rodapé -->
        <div class="user-profile-footer">
            <div class="user-profile-card" onclick="toggleUserProfile()">
                <div class="user-profile-header">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <h6 class="user-name">{{ auth()->user()->name }}</h6>
                        <p class="user-email">{{ auth()->user()->email }}</p>
                    </div>
                    <i class="bi bi-chevron-down user-profile-toggle"></i>
                </div>
                
                <div class="user-profile-options">
                    <a href="{{ route('membership.profile') }}" class="user-option">
                        <i class="bi bi-person-fill"></i>
                        Minha Conta
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="user-option logout" style="background: none; border: none; width: 100%; text-align: left;">
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade centered-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                    </div>
                    <p>Tem certeza que deseja excluir este item?</p>
                    <ul class="text-muted">
                        <li>O item será removido permanentemente</li>
                        <li>Todos os dados associados serão perdidos</li>
                        <li>Esta ação não pode ser revertida</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash-fill"></i>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Estilos específicos para o modal de exclusão */
    #deleteModal.centered-modal .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.8) !important;
        backdrop-filter: blur(5px);
    }
    
    #deleteModal.centered-modal .modal-content {
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        border-radius: 10px;
        background: var(--card-bg) !important;
        color: var(--text-color);
    }
    
    #deleteModal.centered-modal .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: var(--card-bg);
        border-radius: 10px 10px 0 0;
    }
    
    #deleteModal.centered-modal .modal-body {
        background: var(--card-bg);
        color: var(--text-color);
    }
    
    #deleteModal.centered-modal .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: var(--card-bg);
        border-radius: 0 0 10px 10px;
    }
    
    #deleteModal.centered-modal .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    
    #deleteModal.centered-modal .alert-danger {
        background-color: rgba(220, 53, 69, 0.2);
        border-color: rgba(220, 53, 69, 0.3);
        color: #f8d7da;
    }
    
    #deleteModal.centered-modal .text-muted {
        color: var(--text-muted) !important;
    }
    </style>

    <script>
    // Função para obter CSRF token de forma segura
    function getCsrfToken() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfInput = document.querySelector('input[name="_token"]');
        return csrfMeta?.getAttribute('content') || csrfInput?.value || '{{ csrf_token() }}';
    }
    
    // Função para criar e abrir modal de exclusão dinamicamente
    function confirmDelete(url, itemName = 'este item') {
        // Verificar se Bootstrap está disponível
        if (typeof bootstrap === 'undefined') {
            alert('Erro: Bootstrap não está carregado. Recarregue a página.');
            return;
        }
        
        // Remover modal existente se houver
        const existingModal = document.getElementById('deleteModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Obter CSRF token de forma segura
        const csrfToken = getCsrfToken();
        
        console.log('CSRF Token encontrado:', csrfToken ? 'Sim' : 'Não');
        
        // Criar modal dinamicamente
        const modalHtml = `
            <div class="modal fade centered-modal" id="deleteModal" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                Confirmar Exclusão
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                            </div>
                            <p>Tem certeza que deseja excluir <strong>${itemName}</strong>?</p>
                            <ul class="text-muted">
                                <li>O item será removido permanentemente</li>
                                <li>Todos os dados associados serão perdidos</li>
                                <li>Esta ação não pode ser revertida</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </button>
                            <form method="POST" action="${url}" style="display: inline;">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash-fill"></i>
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Adicionar modal ao body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Abrir modal
        const modal = document.getElementById('deleteModal');
        console.log('Modal criado:', modal);
        
        try {
            const deleteModal = new bootstrap.Modal(modal);
            deleteModal.show();
            console.log('Modal aberto com sucesso');
        } catch (error) {
            console.error('Erro ao abrir modal:', error);
            alert('Erro ao abrir modal de exclusão. Tente novamente.');
        }
        
        // Remover modal do DOM quando fechado
        modal.addEventListener('hidden.bs.modal', function () {
            modal.remove();
        });
    }

    // Função para alternar o perfil do usuário
    function toggleUserProfile() {
        const profileCard = document.querySelector('.user-profile-card');
        profileCard.classList.toggle('expanded');
    }
    </script>
    
</body>
</html> 