@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Criar Módulo')

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
                        <a href="{{ route('admin.courses.modules', $course->id) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">Novo Módulo</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="bi bi-plus-circle"></i>
                Criar Novo Módulo
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.courses.modules', $course->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i>
                        Informações do Módulo
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.courses.modules.store', $course->id) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Título do Módulo *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Descreva brevemente o conteúdo deste módulo.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label">Ordem *</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                           id="order" name="order" value="{{ old('order', 1) }}" min="1" required>
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Posição do módulo no curso (1 = primeiro).</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Módulo Ativo
                                        </label>
                                    </div>
                                    <div class="form-text">Módulos inativos não aparecem para os alunos.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.courses.modules', $course->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                Criar Módulo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i>
                        Informações do Curso
                    </h5>
                </div>
                <div class="card-body">
                    <h6>{{ $course->title }}</h6>
                    <p class="text-muted mb-3">{{ Str::limit($course->description, 150) }}</p>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h6 class="mb-1">{{ $course->activeModules->count() }}</h6>
                            <small class="text-muted">Módulos</small>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-1">{{ $course->getTotalLessonsCount() }}</h6>
                            <small class="text-muted">Aulas</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb"></i>
                        Dicas
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Use títulos claros e objetivos
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Organize por ordem lógica
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Descreva o conteúdo do módulo
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            Mantenha módulos ativos
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.border-end {
    border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
}
</style>
@endsection 