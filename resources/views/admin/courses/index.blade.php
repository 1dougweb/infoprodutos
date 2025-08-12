@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Cursos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-book"></i>
            Gerenciar Cursos
        </h1>
        <div>
            <a href="{{ route('admin.products') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar aos Produtos
            </a>
        </div>
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

    <div class="row">
        @foreach($courses as $course)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">{{ $course->title }}</h5>
                            <span class="badge bg-{{ $course->is_active ? 'success' : 'secondary' }}">
                                {{ $course->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        
                        <p class="card-text text-muted mb-3">
                            {{ Str::limit($course->description, 100) }}
                        </p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="mb-1">{{ $course->active_modules_count }}</h6>
                                    <small class="text-muted">Módulos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="mb-1">{{ $course->total_lessons }}</h6>
                                <small class="text-muted">Aulas</small>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.courses.modules', $course->id) }}" class="btn btn-primary">
                                <i class="bi bi-list-ul"></i>
                                Gerenciar Módulos
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i>
                            Criado em {{ $course->created_at->format('d/m/Y') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($courses->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-book" style="font-size: 4rem; color: var(--text-muted);"></i>
            </div>
            <h4>Nenhum curso encontrado</h4>
            <p class="text-muted">Crie produtos primeiro para poder gerenciar os cursos.</p>
            <a href="{{ route('admin.products') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Criar Produto
            </a>
        </div>
    @endif
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.border-end {
    border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>
@endsection 