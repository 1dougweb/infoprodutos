@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Banners')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-white">Gerenciar Banners</h1>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Novo Banner
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Lista de Banners -->
    <div class="row">
        @forelse($banners as $banner)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="banner-preview">
                            <img src="{{ Storage::url($banner->image_path) }}" 
                                 alt="{{ $banner->title }}" 
                                 class="img-fluid rounded"
                                 style="width: 100%; height: 200px; object-fit: cover;">
                        </div>
                        
                        <div class="mt-3">
                            <h5 class="card-title text-white">{{ $banner->title }}</h5>
                            @if($banner->description)
                                <p class="card-text text-muted">{{ Str::limit($banner->description, 100) }}</p>
                            @endif
                            
                            <div class="banner-meta">
                                <span class="badge {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $banner->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <span class="badge bg-info">Ordem: {{ $banner->order }}</span>
                                @if($banner->link)
                                    <span class="badge bg-primary">Com Link</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <form action="{{ route('admin.banners.delete', $banner->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Tem certeza que deseja excluir este banner?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-images fs-1 text-muted mb-3"></i>
                        <h5 class="text-white">Nenhum banner encontrado</h5>
                        <p class="text-muted">Crie seu primeiro banner para começar a personalizar o dashboard.</p>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Criar Banner
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.banner-meta {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.banner-preview {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
}

.banner-preview img {
    transition: transform 0.3s ease;
}

.banner-preview:hover img {
    transform: scale(1.05);
}
</style>
@endsection 