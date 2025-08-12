@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Aulas do Módulo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.products') }}">Produtos</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.courses.modules', $module->digitalProduct->id) }}">{{ $module->digitalProduct->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $module->title }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="bi bi-play-circle"></i>
                Aulas do Módulo: {{ $module->title }}
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.modules.lessons.create', $module->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nova Aula
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
        @foreach($module->activeLessons as $lesson)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $lesson->title }}</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.lessons.edit', $lesson->id) }}">
                                        <i class="bi bi-pencil"></i>
                                        Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger" onclick="confirmDelete('{{ route('admin.lessons.delete', $lesson->id) }}', '{{ $lesson->title }}')">
                                        <i class="bi bi-trash"></i>
                                        Excluir
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <p class="card-text text-muted mb-3">
                            {{ Str::limit($lesson->description, 100) }}
                        </p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="mb-1">{{ $lesson->getFormattedDuration() }}</h6>
                                    <small class="text-muted">Duração</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="mb-1">{{ $lesson->order }}</h6>
                                <small class="text-muted">Ordem</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ $lesson->content_type === 'video' ? 'danger' : ($lesson->content_type === 'iframe' ? 'info' : ($lesson->content_type === 'file' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst($lesson->content_type) }}
                                </span>
                                @if($lesson->is_free)
                                    <span class="badge bg-success">Gratuita</span>
                                @endif
                            </div>
                            <span class="badge bg-{{ $lesson->is_active ? 'success' : 'secondary' }}">
                                {{ $lesson->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($module->activeLessons->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-play-circle" style="font-size: 4rem; color: var(--text-muted);"></i>
            </div>
            <h4>Nenhuma aula encontrada</h4>
            <p class="text-muted">Crie a primeira aula para começar o conteúdo do módulo.</p>
            <a href="{{ route('admin.modules.lessons.create', $module->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Criar Primeira Aula
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
function confirmDelete(url, lessonName) {
    if (confirm(`Tem certeza que deseja excluir a aula "${lessonName}"? Esta ação não pode ser desfeita.`)) {
        window.location.href = url;
    }
}
</script>
@endsection 