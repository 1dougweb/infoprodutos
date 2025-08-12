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
                        <button class="nav-link active" id="mercadopago-tab" data-bs-toggle="tab" data-bs-target="#mercadopago" type="button" role="tab">
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
                    <!-- Mercado Pago -->
                    <div class="tab-pane fade show active" id="mercadopago" role="tabpanel">
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
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="mercadopago_webhook_url" value="{{ str_replace('http://', 'https://', str_replace('127.0.0.1:8000', 'SEU-DOMINIO.com', url('/api/webhooks/mercadopago'))) }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('mercadopago_webhook_url')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">URL para configurar no painel do Mercado Pago</div>
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
                                            <li>Adicione a URL: <code>{{ str_replace('http://', 'https://', str_replace('127.0.0.1:8000', 'SEU-DOMINIO.com', url('/api/webhooks/mercadopago'))) }}</code></li>
                                            <li>Selecione os eventos: <code>payment.created</code>, <code>payment.updated</code></li>
                                            <li>Clique em <strong>Salvar</strong></li>
                                        </ol>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>⚠️ Importante:</strong> Substitua "SEU-DOMINIO.com" pelo seu domínio real. 
                                                A URL deve ser HTTPS e acessível publicamente.
                                            </small>
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
                                                        <div class="current-file-size">{{ \Storage::disk('public')->size($brandingSettings['logo_path']) }} bytes</div>
                                                    </div>
                                                    <img src="{{ Storage::url($brandingSettings['logo_path']) }}" alt="Logo atual" style="max-height: 40px; max-width: 40px; object-fit: contain;">
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
                                                        <div class="current-file-size">{{ \Storage::disk('public')->size($brandingSettings['favicon_path']) }} bytes</div>
                                                    </div>
                                                    <img src="{{ Storage::url($brandingSettings['favicon_path']) }}" alt="Favicon atual" style="max-height: 32px; max-width: 32px; object-fit: contain;">
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
                    <button class="btn btn-outline-info" type="button">
                        <i class="bi bi-arrow-clockwise"></i>
                        Limpar Cache
                    </button>
                    <button class="btn btn-outline-warning" type="button">
                        <i class="bi bi-database"></i>
                        Backup do Sistema
                    </button>
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-file-earmark-text"></i>
                        Logs do Sistema
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
});

// Função para copiar para clipboard
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // Para dispositivos móveis
    
    try {
        document.execCommand('copy');
        
        // Feedback visual
        const button = element.parentElement.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i>';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
        
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
</script>
@endsection 