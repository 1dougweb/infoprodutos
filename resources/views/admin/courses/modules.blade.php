@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Módulos do Curso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.products') }}">Produtos</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $course->title }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="bi bi-list-ul"></i>
                Módulos do Curso: {{ $course->title }}
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.courses.modules.create', $course->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Módulo
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
        @foreach($modules as $module)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $module->title }}</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.modules.lessons', $module->id) }}">
                                        <i class="bi bi-play-circle"></i>
                                        Gerenciar Aulas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.modules.edit', $module->id) }}">
                                        <i class="bi bi-pencil"></i>
                                        Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger" onclick="confirmDelete('{{ route('admin.modules.delete', $module->id) }}', '{{ $module->title }}')">
                                        <i class="bi bi-trash"></i>
                                        Excluir
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <p class="card-text text-muted mb-3">
                            {{ Str::limit($module->description, 100) }}
                        </p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="mb-1">{{ $module->active_lessons_count }}</h6>
                                    <small class="text-muted">Aulas</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="mb-1">{{ $module->order }}</h6>
                                <small class="text-muted">Ordem</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $module->is_active ? 'success' : 'secondary' }}">
                                {{ $module->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                            <a href="{{ route('admin.modules.lessons', $module->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-play-circle"></i>
                                Ver Aulas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($modules->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-list-ul" style="font-size: 4rem; color: var(--text-muted);"></i>
            </div>
            <h4>Nenhum módulo encontrado</h4>
            <p class="text-muted">Crie o primeiro módulo para começar a organizar o curso.</p>
            <a href="{{ route('admin.courses.modules.create', $course->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Criar Primeiro Módulo
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

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 0.5rem;
}

.breadcrumb-item a {
    color: var(--primary-blue);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>

<script>
function confirmDelete(url, moduleName) {
    if (confirm(`Tem certeza que deseja excluir o módulo "${moduleName}"? Esta ação não pode ser desfeita.`)) {
        window.location.href = url;
    }
}
</script>
@endsection 