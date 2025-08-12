@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Criar Aula')

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
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.modules.lessons', $module->id) }}">{{ $module->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">Nova Aula</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="bi bi-plus-circle"></i>
                Criar Nova Aula
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.modules.lessons', $module->id) }}" class="btn btn-outline-secondary">
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
                        <i class="bi bi-play-circle"></i>
                        Informações da Aula
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.modules.lessons.store', $module->id) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Título da Aula *</label>
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
                            <div class="form-text">Descreva brevemente o conteúdo desta aula.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="content_type" class="form-label">Tipo de Conteúdo *</label>
                                    <select class="form-select @error('content_type') is-invalid @enderror" 
                                            id="content_type" name="content_type" required onchange="toggleContentFields()">
                                        <option value="">Selecione o tipo</option>
                                        <option value="text" {{ old('content_type') == 'text' ? 'selected' : '' }}>Texto</option>
                                        <option value="video" {{ old('content_type') == 'video' ? 'selected' : '' }}>Vídeo</option>
                                        <option value="iframe" {{ old('content_type') == 'iframe' ? 'selected' : '' }}>Iframe/Embed</option>
                                        <option value="file" {{ old('content_type') == 'file' ? 'selected' : '' }}>Arquivo para Download</option>
                                    </select>
                                    @error('content_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Duração (minutos) *</label>
                                    <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                           id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 0) }}" min="0" required>
                                    @error('duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos para cada tipo de conteúdo -->
                        <div id="content-fields">
                            <!-- Texto -->
                            <div id="text-fields" class="content-field" style="display: none;">
                                <div class="mb-3">
                                    <label for="content_text" class="form-label">Conteúdo da Aula</label>
                                    <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                              id="content_text" name="content_text" rows="10">{{ old('content_text') }}</textarea>
                                    @error('content_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Digite o conteúdo da aula em formato de texto.</div>
                                </div>
                            </div>

                            <!-- Vídeo -->
                            <div id="video-fields" class="content-field" style="display: none;">
                                <div class="mb-3">
                                    <label for="content_url" class="form-label">URL do Vídeo</label>
                                    <input type="url" class="form-control @error('content_url') is-invalid @enderror" 
                                           id="content_url" name="content_url" value="{{ old('content_url') }}" 
                                           placeholder="https://exemplo.com/video.mp4">
                                    @error('content_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">URL direta do arquivo de vídeo (MP4, WebM, etc.).</div>
                                </div>
                            </div>

                            <!-- Iframe -->
                            <div id="iframe-fields" class="content-field" style="display: none;">
                                <div class="mb-3">
                                    <label for="content_url" class="form-label">URL do Iframe</label>
                                    <input type="url" class="form-control @error('content_url') is-invalid @enderror" 
                                           id="content_url" name="content_url" value="{{ old('content_url') }}" 
                                           placeholder="https://www.youtube.com/embed/...">
                                    @error('content_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">URL de embed (YouTube, Vimeo, etc.).</div>
                                </div>
                            </div>

                            <!-- Arquivo -->
                            <div id="file-fields" class="content-field" style="display: none;">
                                <div class="mb-3">
                                    <label for="content_url" class="form-label">URL do Arquivo</label>
                                    <input type="url" class="form-control @error('content_url') is-invalid @enderror" 
                                           id="content_url" name="content_url" value="{{ old('content_url') }}" 
                                           placeholder="https://exemplo.com/arquivo.pdf">
                                    @error('content_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">URL direta do arquivo para download (PDF, ZIP, etc.).</div>
                                </div>
                            </div>
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
                                    <div class="form-text">Posição da aula no módulo (1 = primeira).</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Aula Ativa
                                        </label>
                                    </div>
                                    <div class="form-text">Aulas inativas não aparecem para os alunos.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_free" name="is_free" 
                                               {{ old('is_free') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_free">
                                            Aula Gratuita
                                        </label>
                                    </div>
                                    <div class="form-text">Aulas gratuitas podem ser acessadas sem comprar o curso.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.modules.lessons', $module->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                Criar Aula
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
                        Informações do Módulo
                    </h5>
                </div>
                <div class="card-body">
                    <h6>{{ $module->title }}</h6>
                    <p class="text-muted mb-3">{{ Str::limit($module->description, 150) }}</p>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h6 class="mb-1">{{ $module->activeLessons->count() }}</h6>
                            <small class="text-muted">Aulas</small>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-1">{{ $module->order }}</h6>
                            <small class="text-muted">Ordem</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb"></i>
                        Dicas de Conteúdo
                    </h5>
                </div>
                <div class="card-body">
                    <div id="text-tips" class="content-tips" style="display: none;">
                        <h6>Para conteúdo de texto:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Use formatação clara
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Inclua exemplos práticos
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Mantenha o foco no tema
                            </li>
                        </ul>
                    </div>
                    
                    <div id="video-tips" class="content-tips" style="display: none;">
                        <h6>Para vídeos:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Use URLs diretas de vídeo
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Formatos: MP4, WebM, OGV
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Mantenha qualidade adequada
                            </li>
                        </ul>
                    </div>
                    
                    <div id="iframe-tips" class="content-tips" style="display: none;">
                        <h6>Para iframes:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                YouTube: /embed/VIDEO_ID
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Vimeo: /video/VIDEO_ID
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Qualquer URL de embed
                            </li>
                        </ul>
                    </div>
                    
                    <div id="file-tips" class="content-tips" style="display: none;">
                        <h6>Para arquivos:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Use URLs diretas de arquivos
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                Formatos: PDF, ZIP, DOC, XLS, etc.
                            </li>
                            <li>
                                <i class="bi bi-check-circle text-success"></i>
                                Arquivos hospedados em nuvem
                            </li>
                        </ul>
                    </div>
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

<script>
function toggleContentFields() {
    const contentType = document.getElementById('content_type').value;
    const contentFields = document.querySelectorAll('.content-field');
    const contentTips = document.querySelectorAll('.content-tips');
    
    // Esconder todos os campos
    contentFields.forEach(field => field.style.display = 'none');
    contentTips.forEach(tip => tip.style.display = 'none');
    
    // Mostrar campos específicos
    if (contentType === 'text') {
        document.getElementById('text-fields').style.display = 'block';
        document.getElementById('text-tips').style.display = 'block';
    } else if (contentType === 'video') {
        document.getElementById('video-fields').style.display = 'block';
        document.getElementById('video-tips').style.display = 'block';
    } else if (contentType === 'iframe') {
        document.getElementById('iframe-fields').style.display = 'block';
        document.getElementById('iframe-tips').style.display = 'block';
    } else if (contentType === 'file') {
        document.getElementById('file-fields').style.display = 'block';
        document.getElementById('file-tips').style.display = 'block';
    }
}

// Executar na carga da página
document.addEventListener('DOMContentLoaded', function() {
    toggleContentFields();
});
</script>
@endsection 