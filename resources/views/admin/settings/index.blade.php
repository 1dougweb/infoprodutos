@extends('membership.layout')

@section('title', 'Configurações')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Mensagens de Sucesso e Erro -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear-fill"></i>
                    Configurações do Sistema
                </h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="bi bi-gear"></i>
                            Geral
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mercadopago-tab" data-bs-toggle="tab" data-bs-target="#mercadopago" type="button" role="tab">
                            <i class="bi bi-credit-card"></i>
                            Mercado Pago
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab">
                            <i class="bi bi-palette"></i>
                            Aparência
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                            <i class="bi bi-image"></i>
                            Marca
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="settingsTabsContent">
                    <!-- Geral -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" action="{{ route('admin.settings.general') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="bi bi-house"></i>
                                                Configurações de Página Inicial
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Tipo de Página Inicial</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="homepage_type" id="homepage_login" value="login" {{ ($generalSettings['homepage_type'] ?? 'login') == 'login' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="homepage_login">
                                                        <strong>Redirecionar para Login</strong>
                                                        <br><small class="text-muted">Os usuários serão direcionados diretamente para a página de login</small>
                                                    </label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="radio" name="homepage_type" id="homepage_custom" value="custom" {{ ($generalSettings['homepage_type'] ?? 'login') == 'custom' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="homepage_custom">
                                                        <strong>Página Personalizada</strong>
                                                        <br><small class="text-muted">Redirecionar para uma URL personalizada</small>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3" id="custom_url_field" style="{{ ($generalSettings['homepage_type'] ?? 'login') == 'custom' ? '' : 'display: none;' }}">
                                                <label for="homepage_url" class="form-label">URL da Página Inicial</label>
                                                <input type="url" class="form-control" id="homepage_url" name="homepage_url" value="{{ $generalSettings['homepage_url'] ?? '' }}" placeholder="https://exemplo.com/pagina-inicial">
                                                <div class="form-text">Digite a URL completa para onde os usuários devem ser redirecionados</div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="homepage_enabled" name="homepage_enabled" {{ ($generalSettings['homepage_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="homepage_enabled">
                                                        <strong>Página Inicial Ativa</strong>
                                                        <br><small class="text-muted">Se desativado, a rota "/" mostrará a página padrão de boas-vindas</small>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="alert alert-info">
                                                <h6><i class="bi bi-info-circle"></i> Como funciona:</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Redirecionar para Login:</strong> Todos os acessos à URL raiz (/) serão redirecionados para /login</li>
                                                    <li><strong>Página Personalizada:</strong> Todos os acessos à URL raiz (/) serão redirecionados para a URL que você especificar</li>
                                                    <li><strong>Página Inicial Desativada:</strong> A rota "/" mostrará a página padrão de boas-vindas do sistema</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="bi bi-check-circle"></i>
                                    Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Mercado Pago -->
                    <div class="tab-pane fade" id="mercadopago" role="tabpanel">
                        <form method="POST" action="{{ route('admin.settings.mercadopago') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mercadopago_access_token" class="form-label">Access Token</label>
                                        <input type="text" class="form-control" id="mercadopago_access_token" name="mercadopago_access_token" value="{{ $mercadopagoSettings['mercadopago_access_token'] }}" placeholder="TEST-xxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                        <div class="form-text">Token de acesso do Mercado Pago</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mercadopago_public_key" class="form-label">Public Key</label>
                                        <input type="text" class="form-control" id="mercadopago_public_key" name="mercadopago_public_key" value="{{ $mercadopagoSettings['mercadopago_public_key'] }}" placeholder="TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                        <div class="form-text">Chave pública do Mercado Pago</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mercadopago_environment" class="form-label">Ambiente</label>
                                        <select class="form-select" id="mercadopago_environment" name="mercadopago_environment">
                                            <option value="sandbox" {{ $mercadopagoSettings['mercadopago_environment'] == 'sandbox' ? 'selected' : '' }}>Sandbox (Teste)</option>
                                            <option value="production" {{ $mercadopagoSettings['mercadopago_environment'] == 'production' ? 'selected' : '' }}>Production (Produção)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                                                            <div class="mb-3">
                                            <label for="mercadopago_webhook_url" class="form-label">Webhook URL</label>
                                            <div class="copy-link form-group">
                                                <input type="text" class="copy-link-input form-control" id="mercadopago_webhook_url" value="{{ $webhookUrl }}" readonly>
                                                <button type="button" class="copy-link-button" onclick="copyToClipboard('mercadopago_webhook_url')" title="Copiar URL">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V2Zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H6ZM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1H2Z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="form-text">
                                                URL para configurar no painel do Mercado Pago
                                                @if($isDevelopment)
                                                    <br><small class="text-warning">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        Modo desenvolvimento detectado. Para produção, configure APP_URL no .env com seu domínio HTTPS.
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                </div>
                            </div>
                            
                            <!-- Configurações de Webhook -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-webhook"></i>
                                        Configurações de Webhook
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mercadopago_webhook_enabled" class="form-label">Webhook Ativo</label>
                                                <select class="form-select" id="mercadopago_webhook_enabled" name="mercadopago_webhook_enabled">
                                                    <option value="1" {{ ($mercadopagoSettings['mercadopago_webhook_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Ativado</option>
                                                    <option value="0" {{ ($mercadopagoSettings['mercadopago_webhook_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Desativado</option>
                                                </select>
                                                <div class="form-text">Ativa ou desativa o recebimento de webhooks</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mercadopago_webhook_secret" class="form-label">Webhook Secret (Opcional)</label>
                                                <input type="password" class="form-control" id="mercadopago_webhook_secret" name="mercadopago_webhook_secret" value="{{ $mercadopagoSettings['mercadopago_webhook_secret'] ?? '' }}" placeholder="Secret para validar webhooks">
                                                <div class="form-text">Secret para validar a autenticidade dos webhooks</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Como configurar o Webhook:</h6>
                                        <ol class="mb-0">
                                            <li>Acesse o <a href="https://www.mercadopago.com.br/developers" target="_blank">painel do Mercado Pago</a></li>
                                            <li>Vá para <strong>Configurações > Webhooks</strong></li>
                                            <li>Adicione a URL: <code>{{ $webhookUrl }}</code></li>
                                            <li>Selecione os eventos: <code>payment.created</code>, <code>payment.updated</code></li>
                                            <li>Clique em <strong>Salvar</strong></li>
                                        </ol>
                                        <div class="mt-2">
                                            @if($isDevelopment)
                                                <small class="text-warning">
                                                    <strong>⚠️ Desenvolvimento:</strong> Esta URL é para desenvolvimento local. 
                                                    Para produção, configure o APP_URL no arquivo .env com seu domínio HTTPS.
                                                </small>
                                            @else
                                                <small class="text-muted">
                                                    <strong>✅ Produção:</strong> URL configurada automaticamente baseada no APP_URL. 
                                                    Certifique-se de que a URL seja HTTPS e acessível publicamente.
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="bi bi-check-circle"></i>
                                    Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aparência -->
                    <div class="tab-pane fade" id="appearance" role="tabpanel">
                        <form method="POST" action="{{ route('admin.settings.appearance') }}">
                            @csrf
                            
                            <!-- Presets de Cores -->
                            <div class="mb-4">
                                <label class="form-label">Presets de Cores</label>
                                <div class="row">
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-primary w-100 preset-btn" data-primary="#007bff" data-secondary="#0056b3">
                                            <div class="preset-color" style="background: #007bff; height: 20px; border-radius: 4px;"></div>
                                            Azul
                                        </button>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-warning w-100 preset-btn" data-primary="#ff6b35" data-secondary="#f7931e">
                                            <div class="preset-color" style="background: #ff6b35; height: 20px; border-radius: 4px;"></div>
                                            Laranja
                                        </button>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-danger w-100 preset-btn" data-primary="#dc3545" data-secondary="#c82333">
                                            <div class="preset-color" style="background: #dc3545; height: 20px; border-radius: 4px;"></div>
                                            Vermelho
                                        </button>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-warning w-100 preset-btn" data-primary="#ffc107" data-secondary="#e0a800">
                                            <div class="preset-color" style="background: #ffc107; height: 20px; border-radius: 4px;"></div>
                                            Dourado
                                        </button>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-outline-info w-100 preset-btn" data-primary="#6f42c1" data-secondary="#5a32a3">
                                            <div class="preset-color" style="background: #6f42c1; height: 20px; border-radius: 4px;"></div>
                                            Roxo
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="primary_color" class="form-label">Cor Primária</label>
                                        <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ $appearanceSettings['primary_color'] }}">
                                        <div class="form-text">Cor principal do tema (afeta sidebar, links, inputs, borders, scrollbar)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="secondary_color" class="form-label">Cor Secundária</label>
                                        <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="{{ $appearanceSettings['secondary_color'] }}">
                                        <div class="form-text">Cor secundária do tema</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="background_color" class="form-label">Cor de Fundo</label>
                                        <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="{{ $appearanceSettings['background_color'] }}">
                                        <div class="form-text">Cor de fundo da aplicação</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="card_background" class="form-label">Cor dos Cards</label>
                                        <input type="color" class="form-control form-control-color" id="card_background" name="card_background" value="{{ $appearanceSettings['card_background'] }}">
                                        <div class="form-text">Cor de fundo dos cards</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    Salvar Aparência
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Marca -->
                    <div class="tab-pane fade" id="branding" role="tabpanel">
                        <form method="POST" action="{{ route('admin.settings.branding') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Nome do Site</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $brandingSettings['site_name'] }}">
                                        <div class="form-text">Nome exibido no cabeçalho</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">Descrição do Site</label>
                                        <input type="text" class="form-control" id="site_description" name="site_description" value="{{ $brandingSettings['site_description'] }}">
                                        <div class="form-text">Descrição para SEO</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo</label>
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                        <div class="form-text">Logo da plataforma (PNG, JPG, SVG) - Máx. 2MB</div>
                                        
                                        @if($brandingSettings['logo_path'])
                                            <div class="current-file mt-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <div class="current-file-name">Logo Atual</div>
                                                        <div class="current-file-size">{{ file_exists(public_path($brandingSettings['logo_path'])) ? filesize(public_path($brandingSettings['logo_path'])) : 0 }} bytes</div>
                                                    </div>
                                                    <img src="{{ url($brandingSettings['logo_path']) }}" alt="Logo atual" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">
                                                        <i class="bi bi-trash"></i>
                                                        Remover Logo
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="favicon" class="form-label">Favicon</label>
                                        <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                        <div class="form-text">Ícone do site (ICO, PNG) - Máx. 1MB</div>
                                        
                                        @if($brandingSettings['favicon_path'])
                                            <div class="current-file mt-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <div class="current-file-name">Favicon Atual</div>
                                                        <div class="current-file-size">{{ file_exists(public_path($brandingSettings['favicon_path'])) ? filesize(public_path($brandingSettings['favicon_path'])) : 0 }} bytes</div>
                                                    </div>
                                                    <img src="{{ url($brandingSettings['favicon_path']) }}" alt="Favicon atual" style="max-height: 32px; max-width: 32px; object-fit: contain;">
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFavicon()">
                                                        <i class="bi bi-trash"></i>
                                                        Remover Favicon
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    Salvar Marca
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i>
                    Informações
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Status do Mercado Pago:</strong>
                    @if($mercadopagoSettings['mercadopago_access_token'])
                        <span class="badge bg-success">Configurado</span>
                    @else
                        <span class="badge bg-danger">Não Configurado</span>
                    @endif
                </div>

                <div class="mb-3">
                    <strong>Ambiente:</strong>
                    <span class="badge bg-{{ $mercadopagoSettings['mercadopago_environment'] == 'production' ? 'success' : 'warning' }}">
                        {{ $mercadopagoSettings['mercadopago_environment'] == 'production' ? 'Produção' : 'Teste' }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Versão do Sistema:</strong>
                    <p class="text-muted">1.0.0</p>
                </div>

                <div class="mb-3">
                    <strong>Última Atualização:</strong>
                    <p class="text-muted">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-tools"></i>
                    Ferramentas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info" type="button" onclick="clearSystemCache()" id="clear-cache-btn">
                        <i class="bi bi-arrow-clockwise"></i>
                        Limpar Cache
                    </button>
                    <a href="{{ route('admin.tools.backup') }}" class="btn btn-outline-warning" onclick="return confirm('Deseja fazer o backup do banco de dados? Pode levar alguns minutos.')">
                        <i class="bi bi-database"></i>
                        Backup do Sistema
                    </a>
                    <button class="btn btn-outline-secondary" type="button" onclick="showSystemLogs()" id="logs-btn">
                        <i class="bi bi-file-earmark-text"></i>
                        Logs do Sistema
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.copy-link {
    position: relative;
    display: flex;
    align-items: center;
}

.copy-link-input {
    padding-right: 50px !important;
    border-radius: 0.375rem !important;
}

.copy-link-button {
    position: absolute;
    right: 2px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none !important;
    outline: none !important;
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
    z-index: 3;
}

.copy-link-button:hover {
    color: {{ $appearanceSettings['primary_color'] }};
    background-color: {{ $appearanceSettings['primary_color'] }}15;
    border: none !important;
    outline: none !important;
}

.copy-link-button:focus {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.copy-link-button:active {
    transform: translateY(-50%) scale(0.95);
    border: none !important;
    outline: none !important;
}

.copy-link-button svg {
    display: block;
}

/* Feedback visual quando copiado */
.copy-link-button.copied {
    color: {{ $appearanceSettings['primary_color'] }};
    background-color: {{ $appearanceSettings['primary_color'] }}25;
    border: none !important;
    outline: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Presets de cores
    const presetButtons = document.querySelectorAll('.preset-btn');
    
    presetButtons.forEach(button => {
        button.addEventListener('click', function() {
            const primaryColor = this.getAttribute('data-primary');
            const secondaryColor = this.getAttribute('data-secondary');
            
            document.getElementById('primary_color').value = primaryColor;
            document.getElementById('secondary_color').value = secondaryColor;
            
            // Feedback visual
            presetButtons.forEach(btn => btn.classList.remove('btn-primary'));
            this.classList.remove('btn-outline-primary', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-info');
            this.classList.add('btn-primary');
        });
    });

    // Controle do campo de URL personalizada
    const homepageRadios = document.querySelectorAll('input[name="homepage_type"]');
    const customUrlField = document.getElementById('custom_url_field');
    
    homepageRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                customUrlField.style.display = 'block';
            } else {
                customUrlField.style.display = 'none';
            }
        });
    });
});

// Função para copiar para clipboard
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // Para dispositivos móveis
    
    try {
        document.execCommand('copy');
        
        // Feedback visual para o novo layout
        const button = element.parentElement.querySelector('.copy-link-button');
        if (button) {
            const originalSvg = button.innerHTML;
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>';
            button.classList.add('copied');
            
            setTimeout(() => {
                button.innerHTML = originalSvg;
                button.classList.remove('copied');
            }, 2000);
        }
        
    } catch (err) {
        console.error('Erro ao copiar: ', err);
    }
}

// Função para remover logo
function removeLogo() {
    if (confirm('Tem certeza que deseja remover o logo?')) {
        fetch('{{ route("admin.settings.logo.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao remover logo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            location.reload();
        });
    }
}

// Função para remover favicon
function removeFavicon() {
    if (confirm('Tem certeza que deseja remover o favicon?')) {
        fetch('{{ route("admin.settings.favicon.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao remover favicon: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            location.reload();
        });
    }
}

// ===== FERRAMENTAS DO SISTEMA =====

// Função para limpar cache
function clearSystemCache() {
    const button = document.getElementById('clear-cache-btn');
    const originalContent = button.innerHTML;
    
    // Mostrar loading
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Limpando...';
    
    fetch('{{ route("admin.tools.clear-cache") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar feedback de sucesso
            button.innerHTML = '<i class="bi bi-check-circle"></i> Cache Limpo!';
            button.classList.remove('btn-outline-info');
            button.classList.add('btn-success');
            
            // Mostrar alerta de sucesso
            showAlert('success', data.message);
            
            // Restaurar botão após 3 segundos
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-info');
                button.disabled = false;
            }, 3000);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        button.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Erro';
        button.classList.remove('btn-outline-info');
        button.classList.add('btn-danger');
        
        showAlert('error', 'Erro ao limpar cache: ' + error.message);
        
        // Restaurar botão após 3 segundos
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.classList.remove('btn-danger');
            button.classList.add('btn-outline-info');
            button.disabled = false;
        }, 3000);
    });
}

// Função para mostrar logs do sistema
function showSystemLogs() {
    const button = document.getElementById('logs-btn');
    const originalContent = button.innerHTML;
    
    // Mostrar loading
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Carregando...';
    
    fetch('{{ route("admin.tools.logs") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalContent;
        button.disabled = false;
        
        if (data.success) {
            // Criar modal para mostrar logs
            showLogsModal(data.logs, data.total_lines);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showAlert('error', 'Erro ao carregar logs: ' + error.message);
    });
}

// Função para mostrar modal de logs
function showLogsModal(logs, totalLines) {
    // Criar modal dinamicamente
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text"></i>
                        Logs do Sistema (últimas 100 linhas de ${totalLines} total)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Mostrando as últimas 100 linhas do arquivo de log. 
                            Para logs completos, acesse o servidor diretamente.
                        </small>
                    </div>
                    <pre style="background: #1e1e1e; color: #fff; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;">${logs || 'Nenhum log encontrado'}</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Mostrar modal usando Bootstrap
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Remover modal do DOM quando fechado
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// Função para mostrar alertas
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        <i class="bi ${iconClass}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Inserir no topo da página
    const container = document.querySelector('.row');
    container.insertBefore(alert, container.firstChild);
    
    // Auto remover após 5 segundos
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection 