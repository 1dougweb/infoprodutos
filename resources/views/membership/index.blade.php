@extends('membership.layout')

@section('title', 'Área de Membros')

@section('content')

<!-- Mensagens de Sessão -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Banner Slider -->
@if($banners->count() > 0)
<div class="banner-slider-container mb-4">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            @foreach($banners as $index => $banner)
                <button type="button" data-bs-target="#bannerCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}"
                        aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        
        <div class="carousel-inner">
            @foreach($banners as $index => $banner)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    @if($banner->link)
                        <a href="{{ $banner->link }}" target="_blank" class="banner-link">
                    @endif
                    
                    <div class="banner-slide" style="background-image: url('{{ Storage::url($banner->image_path) }}')">
                    </div>
                    
                    @if($banner->link)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
        
        @if($banners->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
</div>
@endif
                                                                                                                                                                                                                                                                                                                                                                                                            
<!-- Products Section -->
<div class="section-title">
    <span>Comece por aqui</span>
    <div class="nav-arrows">
        <a href="#" class="nav-arrow">
            <i class="bi bi-chevron-left"></i>
        </a>
        <a href="#" class="nav-arrow">
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>
</div>

<div class="products-grid">
    @foreach($products as $product)
        <div class="product-card {{ !$user->hasPurchased($product->id) ? 'locked' : '' }}">
            @php
                $imageUrl = $product->image && Illuminate\Support\Facades\Storage::disk('public')->exists($product->image) 
                    ? url('/storage/products/images/' . basename($product->image))
                    : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSJ1cmwoI2dyYWRpZW50KSIvPgo8ZGVmcz4KPGxpbmVhckdyYWRpZW50IGlkPSJncmFkaWVudCIgeDE9IjAiIHkxPSIwIiB4Mj0iMjAwIiB5Mj0iMjAwIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+CjxzdG9wIHN0b3AtY29sb3I9IiMwMDdiZmYiLz4KPHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjMDA1NmIzIi8+CjwvbGluZWFyR3JhZGllbnQ+CjwvZGVmcz4KPC9zdmc+';
            @endphp
            <div class="product-image {{ !$user->hasPurchased($product->id) ? 'locked' : '' }}" 
                 style="background-image: url('{{ $imageUrl }}')">
                @if(!$user->hasPurchased($product->id))
                    <div class="lock-icon">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                @endif
                <div class="product-overlay">
                    <div class="product-title-overlay">{{ $product->title }}</div>
                </div>
            </div>

            
            @if($user->hasPurchased($product->id))
                @if($product->product_type === 'digital')
                    <a href="{{ route('membership.digital.product', $product->id) }}" class="action-btn">
                        <i class="bi bi-download"></i> Acessar
                    </a>
                @else
                    <a href="{{ route('membership.course', $product->id) }}" class="action-btn">
                        <i class="bi bi-play-circle"></i> Continuar
                    </a>
                @endif
            @else
                <a href="#" onclick="processCheckout({{ $product->id }}); return false;" class="action-btn buy-btn">
                    <i class="bi bi-cart-plus"></i> Comprar
                </a>
            @endif
        </div>
    @endforeach
</div>

<style>
.section-title {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-arrows {
    display: flex;
    gap: 10px;
}

.nav-arrow {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-arrow:hover {
    background-color: var(--primary-blue);
    color: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.product-card {
    background-color: var(--card-bg);
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
    border: 1px solid rgba(255, 255, 255, 0.1);
    /* Proporção 9:16 para estilo Netflix */
    aspect-ratio: 9/16;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
    border-color: var(--primary-blue);
}

.product-card.locked {
    filter: grayscale(100%);
    opacity: 0.7;
}

.product-image {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.product-image.locked {
    filter: grayscale(100%) brightness(0.5);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-title-overlay {
    color: white;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    padding: 0 15px;
}

.lock-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    background-color: rgba(0, 0, 0, 0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 2;
}



.action-btn {
    position: absolute;
    bottom: 15px;
    left: 15px;
    right: 15px;
    background-color: var(--primary-blue);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    opacity: 0;
    transform: translateY(10px);
    z-index: 10;
}

.product-card:hover .action-btn {
    opacity: 1;
    transform: translateY(0);
}

.action-btn:hover {
    background-color: #0056b3;
    color: white;
    text-decoration: none;
}

.buy-btn {
    background-color: #28a745;
}

.buy-btn:hover {
    background-color: #218838;
}

/* Estilos para o Banner Slider */
.banner-slider-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
    margin-bottom: 30px;
}

#bannerCarousel {
    border-radius: 20px;
}

.carousel-item {
    height: 600px; /* Proporção 9:16 para cards Netflix */
    position: relative;
}

.banner-slide {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.banner-link {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}

.banner-link:hover {
    text-decoration: none;
    color: inherit;
}

.carousel-indicators {
    bottom: 30px;
}

.carousel-indicators button {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.2);
    margin: 0 6px;
    transition: all 0.3s ease;
}

.carousel-indicators button.active {
    background-color: white;
    border-color: white;
    transform: scale(1.2);
}

.carousel-indicators button:hover {
    background-color: rgba(255, 255, 255, 0.7);
    border-color: rgba(255, 255, 255, 0.5);
}

.carousel-control-prev,
.carousel-control-next {
    width: 60px;
    height: 60px;
    background-color: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    margin: 0 30px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background-color: rgba(0, 0, 0, 0.8);
    border-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-50%) scale(1.1);
}

.carousel-control-prev {
    left: 20px;
}

.carousel-control-next {
    right: 20px;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 24px;
    height: 24px;
}

/* Responsividade */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
    }
    
    .product-image {
        min-height: 120px;
    }
    
    .action-btn {
        opacity: 1;
        transform: translateY(0);
        font-size: 11px;
        padding: 8px 12px;
    }
    
    .carousel-item {
        height: 450px; /* Mantém proporção 9:16 */
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
        margin: 0 15px;
    }
}

@media (max-width: 576px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    
    .product-info {
        padding: 12px;
    }
    
    .product-title {
        font-size: 14px;
    }
    
    .product-category {
        font-size: 11px;
    }
    
    .carousel-item {
        height: 375px; /* Mantém proporção 9:16 */
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 45px;
        height: 45px;
        margin: 0 10px;
    }
}
</style>
<!-- Script para processamento de checkout -->
<script>
function processCheckout(productId) {
    // Exibir indicador de carregamento
    const loadingOverlay = document.createElement('div');
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.style.flexDirection = 'column';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border text-light';
    spinner.style.width = '3rem';
    spinner.style.height = '3rem';
    spinner.setAttribute('role', 'status');
    
    const messageDiv = document.createElement('div');
    messageDiv.style.color = 'white';
    messageDiv.style.marginTop = '15px';
    messageDiv.style.fontSize = '16px';
    messageDiv.textContent = 'Preparando checkout...';
    
    loadingOverlay.appendChild(spinner);
    loadingOverlay.appendChild(messageDiv);
    document.body.appendChild(loadingOverlay);
    
    // Usar o método direct-checkout para maior confiabilidade
    try {
        // Primeiro tentar o direct-checkout
        window.location.href = '{{ url('/direct-checkout') }}/' + productId;
        
        // Se após 3 segundos ainda estivermos aqui, tentar método alternativo
        setTimeout(function() {
            if (document.body.contains(loadingOverlay)) {
                messageDiv.textContent = 'Redirecionando para checkout alternativo...';
                window.location.href = '{{ url('/checkout') }}/' + productId;
            }
        }, 3000);
    } catch (error) {
        console.error('Erro no checkout:', error);
        
        // Se falhar, usar método padrão
        messageDiv.textContent = 'Redirecionando para checkout padrão...';
        setTimeout(function() {
            window.location.href = '{{ url('/checkout') }}/' + productId;
        }, 1000);
    }
}
</script>
@endsection 