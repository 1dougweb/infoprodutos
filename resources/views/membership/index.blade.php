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
                                <div class="banner-content">
                                    <h2 class="banner-title">{{ $banner->title }}</h2>
                                    @if($banner->description)
                                        <p class="banner-description">{{ $banner->description }}</p>
                                    @endif
                                </div>
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

<!-- Filtros Elegantes -->
<div class="elegant-filters mb-4">
    <form method="GET" action="{{ route('membership.index') }}" id="filters-form">
        <div class="filters-pills">
            <!-- Filtro por Seção -->
            <div class="filter-pill">
                <select name="section" class="elegant-select" onchange="this.form.submit()">
                    <option value="">Todas as seções</option>
                    @foreach($sections as $section)
                        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
                            {{ $section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Categoria -->
            <div class="filter-pill">
                <select name="category" class="elegant-select" onchange="this.form.submit()">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Tipo -->
            <div class="filter-pill">
                <select name="type" class="elegant-select" onchange="this.form.submit()">
                    <option value="">Todos os tipos</option>
                    <option value="course" {{ request('type') == 'course' ? 'selected' : '' }}>Cursos</option>
                    <option value="digital" {{ request('type') == 'digital' ? 'selected' : '' }}>Downloads</option>
                </select>
            </div>

            <!-- Filtro por Acesso -->
            <div class="filter-pill">
                <select name="access_type" class="elegant-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="free" {{ request('access_type') == 'free' ? 'selected' : '' }}>Gratuitos</option>
                    <option value="paid" {{ request('access_type') == 'paid' ? 'selected' : '' }}>Pagos</option>
                </select>
            </div>

            <!-- Filtro por Status do Usuário -->
            <div class="filter-pill">
                <select name="user_access" class="elegant-select" onchange="this.form.submit()">
                    <option value="">Meu status</option>
                    <option value="purchased" {{ request('user_access') == 'purchased' ? 'selected' : '' }}>Adquiridos</option>
                    <option value="not_purchased" {{ request('user_access') == 'not_purchased' ? 'selected' : '' }}>Não adquiridos</option>
                </select>
            </div>

            <!-- Botão Limpar (só aparece se há filtros ativos) -->
            @if(request()->hasAny(['section', 'category', 'type', 'access_type', 'user_access']))
                <div class="filter-pill">
                    <a href="{{ route('membership.index') }}" class="clear-filters">
                        <i class="bi bi-x-circle"></i> Limpar
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Produtos organizados por seção -->
@php
    $productsBySection = $products->groupBy('section');
@endphp

@if($productsBySection->count() > 0)
    @foreach($productsBySection as $section => $sectionProducts)
        <div class="section-container mb-5">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-collection"></i>
                    {{ $section }}
                    <span class="section-count">({{ $sectionProducts->count() }} {{ $sectionProducts->count() == 1 ? 'item' : 'itens' }})</span>
                </h2>
                
                <!-- Navegação da seção -->
                <div class="section-nav">
                    <button class="section-nav-btn" onclick="scrollSection('{{ $section }}', 'left')">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="section-nav-btn" onclick="scrollSection('{{ $section }}', 'right')">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="products-scroll-container" id="scroll-{{ $section }}">
                <div class="products-grid">
                    @foreach($sectionProducts as $product)
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
            </div>
        </div>
    @endforeach
@else
    <div class="empty-state">
        <div class="empty-icon">
            <i class="bi bi-search"></i>
        </div>
        <h3>Nenhum produto encontrado</h3>
        <p>Tente ajustar os filtros ou volte mais tarde para ver novos conteúdos.</p>
        <a href="{{ route('membership.index') }}" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
        </a>
    </div>
@endif

<style>
/* Estilos base */
:root {
    --primary-blue: {{ \App\Models\Setting::get('primary_color', '#007bff') }};
    --secondary-blue: {{ \App\Models\Setting::get('secondary_color', '#0056b3') }};
    --dark-bg: {{ \App\Models\Setting::get('background_color', '#0f0f0f') }};
    --card-bg: {{ \App\Models\Setting::get('card_background', '#2a2a2a') }};
    --text-light: {{ \App\Models\Setting::get('text_light', '#ffffff') }};
    --text-muted: {{ \App\Models\Setting::get('text_muted', '#b3b3b3') }};
}

/* Banner Styles */
.banner-slider-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.banner-slide {
    height: 300px;
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    align-items: center;
}

.banner-content {
    background: linear-gradient(90deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
    padding: 40px;
    color: white;
    max-width: 50%;
}

.banner-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 15px;
}

.banner-description {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Filtros Elegantes */
.elegant-filters {
    margin-bottom: 30px;
}

.filters-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.filter-pill {
    position: relative;
}

.elegant-select {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: var(--text-light);
    border-radius: 25px;
    padding: 8px 16px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    min-width: 140px;
}

.elegant-select:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: var(--primary-blue);
    transform: translateY(-1px);
}

.elegant-select:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.elegant-select option {
    background: var(--card-bg);
    color: var(--text-light);
    padding: 8px;
}

.clear-filters {
    background: rgba(255, 107, 107, 0.15);
    border: 1px solid rgba(255, 107, 107, 0.3);
    color: #ff6b6b;
    border-radius: 25px;
    padding: 8px 16px;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 6px;
}

.clear-filters:hover {
    background: rgba(255, 107, 107, 0.25);
    transform: translateY(-1px);
    color: #ff6b6b;
}

/* Seções */
.section-container {
    margin-bottom: 50px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 2rem;
    font-weight: bold;
    color: var(--text-light);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-count {
    font-size: 1rem;
    color: var(--text-muted);
    font-weight: normal;
}

.section-nav {
    display: flex;
    gap: 10px;
}

.section-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-nav-btn:hover {
    background: var(--primary-blue);
    transform: scale(1.1);
}

/* Scroll horizontal */
.products-scroll-container {
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 10px;
    scroll-behavior: smooth;
}

.products-scroll-container::-webkit-scrollbar {
    height: 6px;
}

.products-scroll-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.products-scroll-container::-webkit-scrollbar-thumb {
    background: var(--primary-blue);
    border-radius: 3px;
}

/* Grid de produtos - Layout Original */
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
}

.product-card:hover .action-btn {
    opacity: 1;
}

.action-btn:hover {
    background-color: var(--secondary-blue);
    transform: translateY(-2px);
}

.buy-btn {
    background-color: #ffc107;
    color: #000;
}

.buy-btn:hover {
    background-color: #e0a800;
}

/* Estado vazio */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-muted);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: var(--text-light);
    margin-bottom: 15px;
}

/* Responsividade */
@media (max-width: 768px) {
    .banner-content {
        max-width: 100%;
        padding: 20px;
    }
    
    .banner-title {
        font-size: 1.8rem;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .filters-content .row {
        gap: 15px;
    }
    
    .filters-content .col-md-3,
    .filters-content .col-md-2 {
        margin-bottom: 15px;
    }
    
    .product-card {
        flex: 0 0 200px;
    }
}
</style>

<script>
// Toggle de filtros
document.getElementById('toggle-filters').addEventListener('click', function() {
    const content = document.getElementById('filters-content');
    const icon = this.querySelector('i');
    
    content.classList.toggle('collapsed');
    
    if (content.classList.contains('collapsed')) {
        icon.className = 'bi bi-chevron-right';
    } else {
        icon.className = 'bi bi-chevron-down';
    }
});

// Auto-aplicar filtros quando mudar
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filters-form').submit();
    });
});

// Navegação por seção
function scrollSection(section, direction) {
    const container = document.getElementById('scroll-' + section);
    const scrollAmount = 270; // largura do card + gap
    
    if (direction === 'left') {
        container.scrollLeft -= scrollAmount;
    } else {
        container.scrollLeft += scrollAmount;
    }
}

// Função de checkout (mantida do código original)
function processCheckout(productId) {
    // Redirecionar para o checkout
    window.location.href = '/checkout/' + productId;
}

// Inicializar estado dos filtros
document.addEventListener('DOMContentLoaded', function() {
    // Se há filtros ativos, manter filtros abertos
    const hasActiveFilters = {{ 
        request()->hasAny(['section', 'category', 'type', 'access_type', 'user_access']) ? 'true' : 'false' 
    }};
    
    if (!hasActiveFilters) {
        document.getElementById('filters-content').classList.add('collapsed');
        document.getElementById('toggle-filters').querySelector('i').className = 'bi bi-chevron-right';
    }
});
</script>

@endsection