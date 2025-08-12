{{-- 
    Checkout de Pagamento - Mercado Pago
    =====================================
    
    Este arquivo contém o formulário de checkout com duas opções de pagamento:
    1. Cartão de Crédito (via SDK do Mercado Pago)
    2. PIX (via API do Mercado Pago)
    
    Nota: Os estilos CSS foram ajustados para corrigir o padding excessivo
    dos campos do cartão e manter a harmonia visual do formulário.
--}}

@extends('membership.layout')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i>Finalizar Compra</h5>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    @php
                        $imageUrl = $product->image && Illuminate\Support\Facades\Storage::disk('public')->exists($product->image) 
                            ? url('/storage/products/images/' . basename($product->image))
                            : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSJ1cmwoI2dyYWRpZW50KSIvPgo8ZGVmcz4KPGxpbmVhckdyYWRpZW50IGlkPSJncmFkaWVudCIgeDE9IjAiIHkxPSIwIiB4Mj0iMjAwIiB5Mj0iMjAwIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+CjxzdG9wIHN0b3AtY29sb3I9IiMwMDliZmYiLz4KPHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjMDA1NmIzIi8+CjwvbGluZWFyR3JhZGllbnQ+CjwvZGVmcz4KPC9zdmc+';
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $product->title }}" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">
                </div>
                <div class="col-md-6">
                    <h5>{{ $product->title }}</h5>
                    <p>{{ \Illuminate\Support\Str::limit($product->description ?? '', 100) }}</p>
                </div>
                <div class="col-md-3">
                    <div class="price-details">
                        <div class="price-row">
                            <span>Subtotal:</span>
                            <span>R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                        </div>
                        <div class="price-row price-total">
                            <span>Total:</span>
                            <span class="text-primary fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="card-tab" data-bs-toggle="tab" data-bs-target="#card-panel" type="button" role="tab" aria-controls="card-panel" aria-selected="true">
                        <i class="bi bi-credit-card me-2"></i> Cartão
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pix-tab" data-bs-toggle="tab" data-bs-target="#pix-panel" type="button" role="tab" aria-controls="pix-panel" aria-selected="false">
                        <i class="bi bi-qr-code me-2"></i> PIX
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Formulário de Cartão de Crédito -->
                <div class="tab-pane fade show active" id="card-panel" role="tabpanel" aria-labelledby="card-tab">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Preencha os dados do seu cartão para finalizar a compra.
                    </div>

                    <form id="card-payment-form">
                        @csrf
                        <input type="hidden" id="product-id" value="{{ $product->id }}">
                        <input type="hidden" id="order-id" value="{{ $order->id }}">
                        <input type="hidden" id="card-token" name="token">
                        <input type="hidden" id="card-brand" name="card_brand">
                        
                        <div class="mb-3">
                            <label class="form-label" for="cardNumber">Número do Cartão</label>
                            <div class="input-with-icon">
                                <div id="form-checkout__cardNumber" class="form-control"></div>
                                <i class="bi bi-credit-card card-icon"></i>
                            </div>
                            <div class="card-brands mt-2">
                                <img src="https://www.mercadopago.com/org-img/MP3/API/logos/visa.gif" alt="Visa" class="card-brand" data-brand="visa">
                                <img src="https://www.mercadopago.com/org-img/MP3/API/logos/master.gif" alt="Mastercard" class="card-brand" data-brand="master">
                                <img src="https://www.mercadopago.com/org-img/MP3/API/logos/amex.gif" alt="Amex" class="card-brand" data-brand="amex">
                                <img src="https://www.mercadopago.com/org-img/MP3/API/logos/elo.gif" alt="Elo" class="card-brand" data-brand="elo">
                                <img src="https://www.mercadopago.com/org-img/MP3/API/logos/hipercard.gif" alt="Hipercard" class="card-brand" data-brand="hipercard">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cardholderName">Nome no Cartão</label>
                            <input type="text" class="form-control" id="form-checkout__cardholderName" placeholder="Como aparece no cartão">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="cardExpirationDate">Validade (MM/AA)</label>
                                <div id="form-checkout__expirationDate" class="form-control"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="securityCode">Código de Segurança</label>
                                <div class="input-with-icon">
                                    <div id="form-checkout__securityCode" class="form-control"></div>
                                    <i class="bi bi-shield card-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="installments">Parcelas</label>
                            <select class="form-select" id="form-checkout__installments">
                                <option value="1">1x de R$ {{ number_format($product->price, 2, ',', '.') }} sem juros</option>
                                <option value="2">2x de R$ {{ number_format($product->price/2, 2, ',', '.') }} sem juros</option>
                                <option value="3">3x de R$ {{ number_format($product->price/3, 2, ',', '.') }} sem juros</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="identificationNumber">CPF/CNPJ do Titular</label>
                            <div class="input-with-icon">
                                <input type="text" class="form-control" id="identification-input-visible" placeholder="CPF ou CNPJ" maxlength="18">
                                <!-- Campo oculto para o SDK do Mercado Pago -->
                                <input type="hidden" id="form-checkout__identificationNumber" value="">
                            </div>
                            <small class="form-text text-muted">Digite o CPF (11 dígitos) ou CNPJ (14 dígitos)</small>
                        </div>
                        
                        <!-- Campo issuer oculto e opcional para o SDK -->
                        <div class="mb-3" style="display: none;">
                            <label class="form-label" for="issuer">Banco Emissor</label>
                            <select class="form-select" id="form-checkout__issuer">
                                <option value="">Será detectado automaticamente</option>
                            </select>
                        </div>

                        <div id="card-errors" class="alert alert-danger" style="display: none;"></div>

                        <button type="submit" class="btn btn-primary w-100" id="form-checkout__submit">
                            <i class="bi bi-lock-fill me-2"></i> Pagar com Cartão
                        </button>
                    </form>
                </div>

                <!-- Formulário de PIX -->
                <div class="tab-pane fade" id="pix-panel" role="tabpanel" aria-labelledby="pix-tab">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Preencha seus dados e escaneie o QR Code ou copie o código PIX para pagar.
                    </div>

                    <form id="pix-payment-form" class="mb-4">
                        @csrf
                        <input type="hidden" id="pix-product-id" value="{{ $product->id }}">
                        <input type="hidden" id="pix-order-id" value="{{ $order->id }}">

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label" for="pix-first-name">Nome</label>
                                <input type="text" class="form-control" id="pix-first-name" value="{{ explode(' ', auth()->user()->name)[0] ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pix-last-name">Sobrenome</label>
                                <input type="text" class="form-control" id="pix-last-name" value="{{ substr(strstr(auth()->user()->name, ' '), 1) ?? '' }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="pix-email">E-mail</label>
                            <input type="email" class="form-control" id="pix-email" value="{{ auth()->user()->email }}" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label" for="pix-doc-type">Tipo de Documento</label>
                                <select class="form-select" id="pix-doc-type" required>
                                    <option value="CPF" selected>CPF</option>
                                    <option value="CNPJ">CNPJ</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="pix-doc-number">Número do Documento</label>
                                <input type="text" class="form-control" id="pix-doc-number" placeholder="000.000.000-00" required>
                            </div>
                        </div>

                        <button type="button" class="btn btn-lg btn-primary w-100" id="generate-pix-button">
                            <i class="bi bi-qr-code me-2"></i> Gerar QR Code PIX
                        </button>
                    </form>

                    <div id="pix-qr-container" class="d-flex flex-column align-items-center d-none">
                        <div class="bg-dark p-4 rounded mb-4 text-center" style="max-width: 300px; border: 1px solid rgba(255, 255, 255, 0.2);" id="qr-code-container">
                            <div class="spinner-border text-primary" id="pix-loading" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <div id="pix-qr-code" style="display:none;"></div>
                        </div>

                        <div class="text-center" style="max-width: 400px;">
                            <h5>Código PIX</h5>
                            <p>Copie o código abaixo e cole no app do seu banco</p>
                            
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="pix-code" readonly value="Gerando código PIX...">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPixCode()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-clock"></i>
                                Este PIX expira em 30 minutos.
                            </div>
                        </div>
                    </div>
                    <div id="pix-error" class="alert alert-danger mt-3" style="display: none;"></div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center mt-4 text-muted">
                <i class="bi bi-shield-lock me-2 text-primary"></i>
                <span>Pagamento 100% seguro via Mercado Pago</span>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <a href="{{ route('membership.index') }}" class="btn btn-link text-light">
            <i class="bi bi-arrow-left"></i> Voltar para a Área de Membros
        </a>
    </div>
</div>

<!-- Sistema de Notificações Toast -->
<div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <!-- As notificações serão inseridas aqui dinamicamente -->
</div>

<!-- Script do Mercado Pago SDK -->
<script src="https://sdk.mercadopago.com/js/v2"></script>

<style>
/* Estilos para as notificações toast */
.toast-notification {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    margin-bottom: 15px;
    min-width: 300px;
    max-width: 400px;
    animation: slideInRight 0.5s ease-out;
    overflow: hidden;
}

.toast-notification.error {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.toast-notification.info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.toast-notification.warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.toast-header {
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toast-title {
    font-weight: 600;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.toast-body {
    padding: 16px 20px;
    font-size: 14px;
    line-height: 1.5;
}

.toast-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.toast-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-notification.removing {
    animation: slideOutRight 0.5s ease-in forwards;
}

/* Responsividade */
@media (max-width: 768px) {
    .toast-notification {
        min-width: 280px;
        max-width: 90vw;
    }
    
    #notification-container {
        left: 0;
        right: 0;
        padding: 10px;
    }
}
</style>

<script>
// Sistema de Notificações Toast
function showNotification(type, title, message, duration = 5000) {
    const container = document.getElementById('notification-container');
    
    // Criar elemento da notificação
    const notification = document.createElement('div');
    notification.className = `toast-notification ${type}`;
    
    // Determinar ícone baseado no tipo
    let icon = 'bi-check-circle';
    if (type === 'error') icon = 'bi-exclamation-triangle';
    else if (type === 'info') icon = 'bi-info-circle';
    else if (type === 'warning') icon = 'bi-exclamation-circle';
    
    // HTML da notificação
    notification.innerHTML = `
        <div class="toast-header">
            <div class="toast-title">
                <i class="bi ${icon}"></i>
                ${title}
            </div>
            <button class="toast-close" onclick="removeNotification(this.parentElement.parentElement)">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    // Adicionar ao container
    container.appendChild(notification);
    
    // Auto-remover após o tempo especificado
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(notification);
        }, duration);
    }
    
    return notification;
}

function removeNotification(notification) {
    if (notification) {
        notification.classList.add('removing');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }
}

// Funções de validação de CPF e CNPJ (escopo global)
function validateCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
    
    let sum = 0;
    let remainder;
    
    for (let i = 1; i <= 9; i++) {
        sum += parseInt(cpf.substring(i-1, i)) * (11 - i);
    }
    
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.substring(9, 10))) return false;
    
    sum = 0;
    for (let i = 1; i <= 10; i++) {
        sum += parseInt(cpf.substring(i-1, i)) * (12 - i);
    }
    
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.substring(10, 11))) return false;
    
    return true;
}

function validateCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
    
    let size = cnpj.length - 2;
    let numbers = cnpj.substring(0, size);
    let digits = cnpj.substring(size);
    let sum = 0;
    let pos = size - 7;
    
    for (let i = size; i >= 1; i--) {
        sum += numbers.charAt(size - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    if (result !== parseInt(digits.charAt(0))) return false;
    
    size = size + 1;
    numbers = cnpj.substring(0, size);
    sum = 0;
    pos = size - 7;
    
    for (let i = size; i >= 1; i--) {
        sum += numbers.charAt(size - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    if (result !== parseInt(digits.charAt(1))) return false;
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Função helper para obter CSRF token de forma segura
    function getCsrfToken() {
        try {
            // Tentar obter do meta tag
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta && csrfMeta.getAttribute('content')) {
                return csrfMeta.getAttribute('content');
            }
            
            // Tentar obter do input hidden
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput && csrfInput.value) {
                return csrfInput.value;
            }
            
            // Fallback para o token do Blade
            return '{{ csrf_token() }}';
        } catch (error) {
            console.warn('Erro ao obter CSRF token:', error);
            // Fallback final
            return '{{ csrf_token() }}';
        }
    }

    // Verificar se o produto e pedido estão definidos
    if (!{{ $product->id }} || !{{ $order->id }}) {
        showNotification('error', 'Erro de Dados', 'Dados do produto ou pedido não encontrados. Por favor, tente novamente.', 8000);
        setTimeout(() => {
            window.location.href = '{{ route('membership.index') }}';
        }, 3000);
        return;
    }

    // Inicializar o SDK do Mercado Pago
    const mp = new MercadoPago('{{ \App\Models\Setting::get('mercadopago_public_key', 'TEST-758********96044') }}', {
        locale: 'pt-BR'
    });

    // Configurar botão para gerar PIX
    document.getElementById('generate-pix-button').addEventListener('click', function() {
        generatePixQRCode();
    });
    
    // Máscara para CPF/CNPJ do PIX
    document.getElementById('pix-doc-number').addEventListener('input', function(e) {
        const docType = document.getElementById('pix-doc-type').value;
        let value = e.target.value.replace(/\D/g, '');
        
        if (docType === 'CPF') {
            // Máscara de CPF: 000.000.000-00
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{0,3}).*/, '$1.$2');
            }
        } else {
            // Máscara de CNPJ: 00.000.000/0000-00
            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            } else if (value.length > 8) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4}).*/, '$1.$2.$3/$4');
            } else if (value.length > 5) {
                value = value.replace(/^(\d{2})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,3}).*/, '$1.$2');
            }
        }
        
        e.target.value = value;
    });
    
    // Máscara para CPF/CNPJ do cartão de crédito
    document.getElementById('identification-input-visible').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        // Detectar se é CPF (11 dígitos) ou CNPJ (14 dígitos)
        if (value.length <= 11) {
            // Máscara de CPF: 000.000.000-00
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{0,3}).*/, '$1.$2');
            }
        } else {
            // Máscara de CNPJ: 00.000.000/0000-00
            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            } else if (value.length > 8) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4}).*/, '$1.$2.$3/$4');
            } else if (value.length > 5) {
                value = value.replace(/^(\d{2})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,3}).*/, '$1.$2');
            }
        }
        
        e.target.value = value;
        
        // Atualizar o campo oculto com apenas números
        const docDigits = value.replace(/\D/g, '');
        document.getElementById('form-checkout__identificationNumber').value = docDigits;
    });
    
    // Atualizar máscara quando mudar o tipo de documento
    document.getElementById('pix-doc-type').addEventListener('change', function() {
        const docNumberInput = document.getElementById('pix-doc-number');
        docNumberInput.value = '';
        
        if (this.value === 'CPF') {
            docNumberInput.placeholder = '000.000.000-00';
            docNumberInput.maxLength = 14;
        } else {
            docNumberInput.placeholder = '00.000.000/0000-00';
            docNumberInput.maxLength = 18;
        }
    });

    // Inicializar campos do cartão
    try {
        const cardForm = mp.cardForm({
            amount: '{{ $product->price }}',
            iframe: true,
            autoMount: true,
            form: {
                id: 'card-payment-form',
                cardNumber: {
                    id: 'form-checkout__cardNumber',
                    placeholder: '1234 5678 9012 3456',
                },
                expirationDate: {
                    id: 'form-checkout__expirationDate',
                    placeholder: 'MM/AA',
                },
                securityCode: {
                    id: 'form-checkout__securityCode',
                    placeholder: 'CVV',
                },
                cardholderName: {
                    id: 'form-checkout__cardholderName',
                    placeholder: 'Nome como está no cartão',
                },
                installments: {
                    id: 'form-checkout__installments',
                    placeholder: 'Parcelas',
                },
                identificationNumber: {
                    id: 'form-checkout__identificationNumber',
                    placeholder: 'CPF ou CNPJ',
                },
                issuer: {
                    id: 'form-checkout__issuer',
                    placeholder: 'Banco emissor (opcional)',
                },
            },
            callbacks: {
                onFormMounted: error => {
                    if (error) {
                        console.error('Form Mounted error: ', error);
                        showNotification('error', 'Erro no Formulário', 
                            'Erro ao carregar o formulário. Por favor, tente novamente.', 8000);
                        showCardError('Erro ao carregar o formulário. Por favor, tente novamente.');
                    } else {
                        console.log('Formulário de cartão carregado com sucesso');
                        showNotification('info', 'Formulário Carregado', 
                            'Formulário de cartão carregado com sucesso! Preencha os dados.', 3000);
                    }
                },
                onSubmit: event => {
                    event.preventDefault();
                    
                    // Validar apenas campos que podemos acessar diretamente
                    const cardholderName = document.getElementById('form-checkout__cardholderName').value;
                    const identificationNumber = document.getElementById('identification-input-visible').value;
                    
                    if (!cardholderName.trim()) {
                        showCardError('Por favor, preencha o nome no cartão.');
                        return;
                    }
                    
                    if (!identificationNumber.trim()) {
                        showCardError('Por favor, preencha o CPF ou CNPJ.');
                        return;
                    }
                    
                    // Obter apenas os dígitos do documento
                    const docDigits = identificationNumber.replace(/\D/g, '');
                    
                    // Validar se tem o número correto de dígitos
                    if (docDigits.length !== 11 && docDigits.length !== 14) {
                        showCardError('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos.');
                        return;
                    }
                    
                    // Validar o documento usando as funções apropriadas
                    let isValidDoc = false;
                    let docType = '';
                    
                    if (docDigits.length === 11) {
                        isValidDoc = validateCPF(identificationNumber);
                        docType = 'CPF';
                    } else {
                        isValidDoc = validateCNPJ(identificationNumber);
                        docType = 'CNPJ';
                    }
                    
                    if (!isValidDoc) {
                        showCardError(`Por favor, informe um ${docType} válido.`);
                        return;
                    }
                    
                    // Se chegou até aqui, os campos estão válidos
                    // Agora deixar o Mercado Pago validar o cartão
                    console.log('Campos válidos, deixando Mercado Pago validar o cartão...');
                    
                    // Não fazer nada aqui - deixar o Mercado Pago processar
                    // O processamento será feito no onCardTokenReceived
                },
                onFetching: (resource) => {
                    // Mostrar loading quando estiver buscando recursos
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.style.display = resource === 'payment' ? 'block' : 'none';
                    }
                },
                onCardTokenReceived: (error, token) => {
                    if (error) {
                        console.error('Token error: ', error);
                        console.error('Detalhes do erro:', JSON.stringify(error, null, 2));
                        
                        // Tratar erros específicos do Mercado Pago
                        if (Array.isArray(error) && error.length > 0) {
                            const firstError = error[0];
                            if (firstError.code === '324' && firstError.message.includes('identificationNumber')) {
                                const errorMessage = 'CPF/CNPJ inválido. Por favor, verifique o número informado.';
                                showNotification('error', 'CPF/CNPJ Inválido', errorMessage, 8000);
                                showCardError(errorMessage);
                            } else {
                                const errorMessage = 'Erro ao validar cartão: ' + firstError.message;
                                showNotification('error', 'Erro no Cartão', errorMessage, 8000);
                                showCardError(errorMessage);
                            }
                        } else {
                            const errorMessage = 'Erro ao validar cartão. Por favor, verifique os dados.';
                            showNotification('error', 'Erro no Cartão', errorMessage, 8000);
                            showCardError(errorMessage);
                        }
                        return;
                    }
                    
                    console.log('Token do cartão gerado com sucesso:', token);
                    showNotification('success', 'Cartão Validado', 
                        'Cartão validado com sucesso! Processando pagamento...', 3000);
                    
                    // Agora processar o pagamento com o token válido
                    processPaymentWithToken(token);
                },
                onPaymentMethodsReceived: (error, methods) => {
                    if (error) {
                        console.error('Payment methods error: ', error);
                    }
                },
                onIssuersReceived: (error, issuers) => {
                    if (error) {
                        console.error('Issuers error: ', error);
                    }
                },
                onInstallmentsReceived: (error, installments) => {
                    if (error) {
                        console.error('Installments error: ', error);
                    }
                }
            }
        });
        
        // Tornar o cardForm global para acesso em outras funções
        window.cardForm = cardForm;
        
    } catch (error) {
        console.error('Erro ao inicializar o formulário de cartão:', error);
        showCardError('Erro ao carregar o formulário de cartão. Por favor, tente novamente.');
    }

    // Removendo as funções de validação daqui, pois serão definidas globalmente

    // Verificar status do pagamento
    function startPaymentCheck(orderId, paymentId) {
        console.log('Iniciando verificação automática do PIX - Order ID:', orderId, 'Payment ID:', paymentId);
        
        // Mostrar mensagem de verificação
        showNotification('info', 'Verificando PIX', 'Verificando status do pagamento automaticamente...', 0);
        
        // Verificar a cada 3 segundos
        const checkInterval = setInterval(() => {
            console.log('Verificando status do pagamento PIX...');
            
            // Fazer requisição para verificar status
            fetch('/api/payment/check-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    order_id: orderId,
                    payment_id: paymentId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Status do pagamento:', data);
                
                if (data.status === 'approved') {
                    clearInterval(checkInterval);
                    
                    // Remover notificação de verificação
                    const checkingNotifications = document.querySelectorAll('.toast-notification.info');
                    checkingNotifications.forEach(notification => {
                        if (notification.textContent.includes('Verificando PIX')) {
                            removeNotification(notification);
                        }
                    });
                    
                    // Mostrar notificação de sucesso
                    showNotification('success', 'PIX Aprovado!', 'Pagamento PIX aprovado! Redirecionando...', 3000);
                    
                    // Redirecionar para página de sucesso
                    setTimeout(() => {
                        window.location.href = '/payment/success?payment_id=' + paymentId + '&payment_type=pix';
                    }, 2000);
                    
                } else if (data.status === 'rejected' || data.status === 'cancelled') {
                    clearInterval(checkInterval);
                    
                    // Remover notificação de verificação
                    const checkingNotifications = document.querySelectorAll('.toast-notification.info');
                    checkingNotifications.forEach(notification => {
                        if (notification.textContent.includes('Verificando PIX')) {
                            removeNotification(notification);
                        }
                    });
                    
                    // Mostrar erro
                    showNotification('error', 'PIX Rejeitado', 'Pagamento PIX foi rejeitado. Tente novamente.', 8000);
                    showPixError('Pagamento PIX rejeitado. Tente novamente ou use outro método de pagamento.');
                }
                // Se ainda estiver em processamento, continuar verificando
            })
            .catch(error => {
                console.error('Erro ao verificar status:', error);
                // Não parar a verificação em caso de erro
            });
        }, 3000); // Verificar a cada 3 segundos
        
        // Parar verificação após 10 minutos (600 segundos)
        setTimeout(() => {
            clearInterval(checkInterval);
            
            // Remover notificação de verificação
            const checkingNotifications = document.querySelectorAll('.toast-notification.info');
            checkingNotifications.forEach(notification => {
                if (notification.textContent.includes('Verificando PIX')) {
                    removeNotification(notification);
                }
            });
            
            // Mostrar mensagem de timeout
            showNotification('warning', 'Verificação Expirada', 'Verificação automática expirou. Verifique manualmente o status do pagamento.', 8000);
        }, 600000); // 10 minutos
        
        return checkInterval; // Retornar o intervalo para poder parar se necessário
    }

    // Função para gerar QR code PIX
    function generatePixQRCode() {
        // Validar formulário
        const firstName = document.getElementById('pix-first-name').value.trim();
        const lastName = document.getElementById('pix-last-name').value.trim();
        const email = document.getElementById('pix-email').value.trim();
        const docType = document.getElementById('pix-doc-type').value;
        const docNumber = document.getElementById('pix-doc-number').value.trim();
        
        // Validação básica
        if (!firstName || !lastName || !email || !docNumber) {
            showPixError('Por favor, preencha todos os campos obrigatórios.');
            return;
        }
        
        // Validar e-mail
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showPixError('Por favor, informe um e-mail válido.');
            return;
        }
        
        // Validar CPF/CNPJ
        if (docType === 'CPF' && !validateCPF(docNumber)) {
            showPixError('Por favor, informe um CPF válido.');
            return;
        } else if (docType === 'CNPJ' && !validateCNPJ(docNumber)) {
            showPixError('Por favor, informe um CNPJ válido.');
            return;
        }
        
        // Mostrar loading e esconder erro
        document.getElementById('pix-error').style.display = 'none';
        document.getElementById('pix-loading').style.display = 'block';
        document.getElementById('pix-qr-code').style.display = 'none';
        
        // Mostrar container do QR code
        const pixQrContainer = document.getElementById('pix-qr-container');
        pixQrContainer.classList.remove('d-none');
        pixQrContainer.classList.add('d-flex');
        
        // Obter dados do formulário
        const productId = document.getElementById('pix-product-id').value;
        const orderId = document.getElementById('pix-order-id').value;
        
        // Fazer requisição para gerar QR code PIX
        fetch('{{ route('payment.generate-pix') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                order_id: orderId,
                payment_method: 'pix',
                payer: {
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    identification: {
                        type: docType,
                        number: docNumber.replace(/\D/g, '')
                    }
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pix_data) {
                // Mostrar notificação de sucesso
                showNotification('success', 'PIX Gerado!', 
                    'QR Code PIX criado com sucesso! Escaneie ou copie o código para pagar.', 5000);
                
                // Se temos o QR code em base64
                if (data.pix_data.qr_code_base64) {
                    // Verificar se o base64 é válido (deve ter pelo menos 20 caracteres)
                    if (data.pix_data.qr_code_base64.length > 20) {
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="data:image/png;base64,${data.pix_data.qr_code_base64}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = data.pix_data.qr_code || '';
                        console.log('Usando QR code base64 da resposta');
                    } else {
                        // Base64 inválido, gerar QR code a partir do código PIX
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(data.pix_data.qr_code || '');
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = data.pix_data.qr_code || '';
                        console.log('Base64 inválido, gerando QR code a partir do código PIX');
                    }
                } 
                // Se temos apenas o código QR sem imagem
                else if (data.pix_data.qr_code) {
                    console.log('Usando código PIX sem base64');
                    // Verificar se o código PIX é válido (deve começar com 00020126 para PIX padrão)
                    if (data.pix_data.qr_code.startsWith('00020126')) {
                        // Código PIX padrão EMV
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(data.pix_data.qr_code);
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = data.pix_data.qr_code;
                        console.log('Código PIX EMV detectado');
                    }
                    // Verificar se o código QR é uma URL
                    else if (data.pix_data.qr_code.startsWith('http')) {
                        // Usar QR Server API para gerar QR code a partir da URL
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(data.pix_data.qr_code);
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        // Mostrar a URL como código PIX
                        document.getElementById('pix-code').value = data.pix_data.qr_code;
                        console.log('URL PIX detectada');
                    } else {
                        // Usar QR Server API para gerar QR code a partir do código PIX
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(data.pix_data.qr_code);
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = data.pix_data.qr_code;
                        console.log('Código PIX genérico detectado');
                    }
                }
                // Fallback para QR code genérico
                else {
                    console.log('Usando fallback para QR code');
                    // Usar o ticket_url se disponível
                    if (data.pix_data.ticket_url) {
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(data.pix_data.ticket_url);
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = data.pix_data.ticket_url;
                        console.log('Usando ticket_url para QR code');
                    } else {
                        // Último recurso - QR code genérico
                        const orderId = data.order_id || data.preference_id;
                        const fallbackPixCode = 'https://www.mercadopago.com.br/checkout/v1/redirect?order_id=' + orderId;
                        const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                            encodeURIComponent(fallbackPixCode);
                        
                        document.getElementById('pix-qr-code').innerHTML = `
                            <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                        `;
                        document.getElementById('pix-code').value = fallbackPixCode;
                        console.log('Usando fallback genérico para QR code');
                    }
                }
                
                // Esconder loading e mostrar QR code
                document.getElementById('pix-loading').style.display = 'none';
                document.getElementById('pix-qr-code').style.display = 'block';
                
                // Iniciar verificação de pagamento
                const paymentId = data.payment_id || data.mercadopago_payment_id;
                startPaymentCheck(orderId, paymentId);
                
                // Mostrar notificação de instruções
                setTimeout(() => {
                    showNotification('info', 'PIX Gerado com Sucesso', 
                        'Escaneie o QR Code ou copie o código PIX para pagar. Aguarde a confirmação.', 8000);
                }, 1000);
            } else {
                // Exibir mensagem de erro
                const errorMessage = 'Erro ao gerar QR code PIX: ' + (data.error || 'Tente novamente.');
                showNotification('error', 'Erro no PIX', errorMessage, 8000);
                showPixError(errorMessage);
                document.getElementById('pix-qr-code').style.display = 'block';
                document.getElementById('pix-loading').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro ao gerar QR code PIX:', error);
            
            // Exibir mensagem de erro
            const errorMessage = 'Erro ao gerar QR code PIX: ' + error.message;
            showNotification('error', 'Erro no PIX', errorMessage, 8000);
            showPixError(errorMessage);
            document.getElementById('pix-qr-code').style.display = 'block';
            document.getElementById('pix-loading').style.display = 'none';
        });
    }

    // Remover a função antiga que estava fora do escopo

    // Verificar status do pagamento
});

// Mostrar erro no formulário de cartão
function showCardError(message) {
    const errorElement = document.getElementById('card-errors');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    // Esconder após 5 segundos
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

// Processar pagamento com token válido
function processPaymentWithToken(token) {
    try {
        // Mostrar loading
        const loadingOverlay = showLoading('Processando pagamento...');
        
        // Mostrar notificação de processamento
        showNotification('info', 'Processando Pagamento', 
            'Aguarde enquanto processamos seu pagamento...', 0);
        
        // Obter dados do formulário
        const cardholderName = document.getElementById('form-checkout__cardholderName').value;
        const identificationNumber = document.getElementById('identification-input-visible').value;
        const docDigits = identificationNumber.replace(/\D/g, '');
        const docType = docDigits.length === 11 ? 'CPF' : 'CNPJ';
        
        // Obter dados do cartão do Mercado Pago
        const cardForm = window.cardForm;
        if (!cardForm) {
            throw new Error('Formulário de cartão não encontrado');
        }
        
        // Obter dados do cartão
        const cardData = cardForm.getCardFormData();
        console.log('Dados do cartão obtidos:', cardData);
        
        // Enviar dados para o backend
        fetch('{{ route('payment.create-order') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                product_id: document.getElementById('product-id').value,
                order_id: document.getElementById('order-id').value,
                payment_method: 'credit_card',
                card_token: token,
                card_brand: cardData.paymentMethodId || 'visa',
                installments: cardData.installments || 1,
                card_holder_name: cardholderName,
                card_holder_doc: docDigits,
                identification_type: docType
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Resposta do backend:', data);
            if (data.success) {
                // Remover notificação de processamento
                const processingNotifications = document.querySelectorAll('.toast-notification.info');
                processingNotifications.forEach(notification => {
                    if (notification.textContent.includes('Processando Pagamento')) {
                        removeNotification(notification);
                    }
                });
                
                // Mostrar notificação de sucesso
                showNotification('success', 'Pagamento Aprovado!', 
                    'Seu cartão foi processado com sucesso! Redirecionando...', 3000);
                
                // Redirecionar para página de sucesso
                const paymentId = data.payment_id || data.mercadopago_payment_id;
                const orderId = document.getElementById('order-id').value;
                
                console.log('Payment ID para redirecionamento:', paymentId);
                console.log('Order ID como fallback:', orderId);
                
                // Aguardar um pouco para mostrar a notificação
                setTimeout(() => {
                    // Se não temos payment_id, usar order_id e redirecionar para área de membros
                    if (!paymentId) {
                        console.log('Payment ID não encontrado, redirecionando para área de membros');
                        window.location.href = '{{ route('membership.index') }}?payment_success=true&order_id=' + orderId;
                    } else {
                        console.log('Redirecionando para página de sucesso com payment_id:', paymentId);
                        window.location.href = '{{ url('/payment/success') }}?payment_id=' + 
                            paymentId + '&preference_id=' + paymentId + '&payment_type=credit_card';
                    }
                }, 2000);
            } else {
                // Remover notificação de processamento
                const processingNotifications = document.querySelectorAll('.toast-notification.info');
                processingNotifications.forEach(notification => {
                    if (notification.textContent.includes('Processando Pagamento')) {
                        removeNotification(notification);
                    }
                });
                
                // Mostrar erro
                loadingOverlay.remove();
                showNotification('error', 'Erro no Pagamento', 
                    data.error || 'Erro ao processar pagamento. Por favor, tente novamente.', 8000);
                showCardError(data.error || 'Erro ao processar pagamento. Por favor, tente novamente.');
            }
        })
        .catch(error => {
            console.error('Erro ao processar pagamento:', error);
            loadingOverlay.remove();
            showCardError('Erro ao processar pagamento. Por favor, tente novamente.');
        });
    } catch (error) {
        console.error('Erro ao processar formulário:', error);
        showCardError('Erro ao processar pagamento. Por favor, tente novamente.');
    }
}

// Gerar QR Code PIX
function generatePixQRCode() {
    // Validar formulário
    const firstName = document.getElementById('pix-first-name').value.trim();
    const lastName = document.getElementById('pix-last-name').value.trim();
    const email = document.getElementById('pix-email').value.trim();
    const docType = document.getElementById('pix-doc-type').value;
    const docNumber = document.getElementById('pix-doc-number').value.trim();
    
    // Validação básica
    if (!firstName || !lastName || !email || !docNumber) {
        showPixError('Por favor, preencha todos os campos obrigatórios.');
        return;
    }
    
    // Validar e-mail
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showPixError('Por favor, informe um e-mail válido.');
        return;
    }
    
    // Validar CPF/CNPJ
    if (docType === 'CPF' && !validateCPF(docNumber)) {
        showPixError('Por favor, informe um CPF válido.');
        return;
    } else if (docType === 'CNPJ' && !validateCNPJ(docNumber)) {
        showPixError('Por favor, informe um CNPJ válido.');
        return;
    }
    
    // Mostrar loading e esconder erro
    document.getElementById('pix-error').style.display = 'none';
    document.getElementById('pix-loading').style.display = 'block';
    document.getElementById('pix-qr-code').style.display = 'none';
    
    // Mostrar container do QR code
    const pixQrContainer = document.getElementById('pix-qr-container');
    pixQrContainer.classList.remove('d-none');
    pixQrContainer.classList.add('d-flex');
    
    // Obter dados do formulário
    const productId = document.getElementById('pix-product-id').value;
    const orderId = document.getElementById('pix-order-id').value;
    
    // Fazer requisição para gerar QR code PIX
    fetch('{{ route('payment.generate-pix') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            order_id: orderId,
            payment_method: 'pix',
            payer: {
                first_name: firstName,
                last_name: lastName,
                email: email,
                identification: {
                    type: docType,
                    number: docNumber.replace(/\D/g, '')
                }
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.pix_data) {
            // Mostrar notificação de sucesso
            showNotification('success', 'PIX Gerado!', 
                'QR Code PIX criado com sucesso! Escaneie ou copie o código para pagar.', 5000);
            
            // Se temos o QR code em base64
            if (data.pix_data.qr_code_base64) {
                // Verificar se o base64 é válido (deve ter pelo menos 20 caracteres)
                if (data.pix_data.qr_code_base64.length > 20) {
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="data:image/png;base64,${data.pix_data.qr_code_base64}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = data.pix_data.qr_code || '';
                    console.log('Usando QR code base64 da resposta');
                } else {
                    // Base64 inválido, gerar QR code a partir do código PIX
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(data.pix_data.qr_code || '');
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = data.pix_data.qr_code || '';
                    console.log('Base64 inválido, gerando QR code a partir do código PIX');
                }
            } 
            // Se temos apenas o código QR sem imagem
            else if (data.pix_data.qr_code) {
                console.log('Usando código PIX sem base64');
                // Verificar se o código PIX é válido (deve começar com 00020126 para PIX padrão)
                if (data.pix_data.qr_code.startsWith('00020126')) {
                    // Código PIX padrão EMV
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(data.pix_data.qr_code);
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = data.pix_data.qr_code;
                    console.log('Código PIX EMV detectado');
                }
                // Verificar se o código QR é uma URL
                else if (data.pix_data.qr_code.startsWith('http')) {
                    // Usar QR Server API para gerar QR code a partir da URL
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(data.pix_data.qr_code);
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    // Mostrar a URL como código PIX
                    document.getElementById('pix-code').value = data.pix_data.qr_code;
                    console.log('URL PIX detectada');
                } else {
                    // Usar QR Server API para gerar QR code a partir do código PIX
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(data.pix_data.qr_code);
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = data.pix_data.qr_code;
                    console.log('Código PIX genérico detectado');
                }
            }
            // Fallback para QR code genérico
            else {
                console.log('Usando fallback para QR code');
                // Usar o ticket_url se disponível
                if (data.pix_data.ticket_url) {
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(data.pix_data.ticket_url);
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = data.pix_data.ticket_url;
                    console.log('Usando ticket_url para QR code');
                } else {
                    // Último recurso - QR code genérico
                    const orderId = data.order_id || data.preference_id;
                    const fallbackPixCode = 'https://www.mercadopago.com.br/checkout/v1/redirect?order_id=' + orderId;
                    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + 
                        encodeURIComponent(fallbackPixCode);
                    
                    document.getElementById('pix-qr-code').innerHTML = `
                        <img src="${qrCodeUrl}" alt="QR Code PIX" class="img-fluid">
                    `;
                    document.getElementById('pix-code').value = fallbackPixCode;
                    console.log('Usando fallback genérico para QR code');
                }
            }
            
            // Esconder loading e mostrar QR code
            document.getElementById('pix-loading').style.display = 'none';
            document.getElementById('pix-qr-code').style.display = 'block';
            
            // Iniciar verificação de pagamento
            const paymentId = data.payment_id || data.mercadopago_payment_id;
            startPaymentCheck(orderId, paymentId);
            
            // Mostrar notificação de instruções
            setTimeout(() => {
                showNotification('info', 'PIX Gerado com Sucesso', 
                    'Escaneie o QR Code ou copie o código PIX para pagar. Aguarde a confirmação.', 8000);
            }, 1000);
        } else {
            // Exibir mensagem de erro
            const errorMessage = 'Erro ao gerar QR code PIX: ' + (data.error || 'Tente novamente.');
            showNotification('error', 'Erro no PIX', errorMessage, 8000);
            showPixError(errorMessage);
            document.getElementById('pix-qr-code').style.display = 'block';
            document.getElementById('pix-loading').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Erro ao gerar QR code PIX:', error);
        
        // Exibir mensagem de erro
        const errorMessage = 'Erro ao gerar QR code PIX: ' + error.message;
        showNotification('error', 'Erro no PIX', errorMessage, 8000);
        showPixError(errorMessage);
        document.getElementById('pix-qr-code').style.display = 'block';
        document.getElementById('pix-loading').style.display = 'none';
    });
}

// Mostrar erro no formulário PIX
function showPixError(message) {
    const errorElement = document.getElementById('pix-error');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    // Esconder QR code container
    document.getElementById('pix-qr-container').style.display = 'none';
    
    // Scroll para o erro
    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Esconder após 5 segundos
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

// Copiar código PIX
function copyPixCode() {
    const pixCode = document.getElementById('pix-code');
    pixCode.select();
    document.execCommand('copy');
    
    // Feedback visual
    const copyButton = document.querySelector('.btn-outline-secondary');
    const originalIcon = copyButton.innerHTML;
    copyButton.innerHTML = '<i class="bi bi-check"></i>';
    
    // Mostrar notificação de sucesso
    showNotification('success', 'Código Copiado!', 
        'Código PIX copiado para a área de transferência!', 3000);
    
    setTimeout(() => {
        copyButton.innerHTML = originalIcon;
    }, 2000);
}

// Mostrar overlay de carregamento
function showLoading(message) {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
    loadingOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    loadingOverlay.style.zIndex = '9999';
    
    const loadingContent = document.createElement('div');
    loadingContent.className = 'd-flex flex-column align-items-center';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border text-light mb-3';
    spinner.setAttribute('role', 'status');
    
    const loadingText = document.createElement('div');
    loadingText.className = 'text-light';
    loadingText.textContent = message || 'Carregando...';
    
    loadingContent.appendChild(spinner);
    loadingContent.appendChild(loadingText);
    loadingOverlay.appendChild(loadingContent);
    document.body.appendChild(loadingOverlay);
    
    return loadingOverlay;
}
</script>
@endsection