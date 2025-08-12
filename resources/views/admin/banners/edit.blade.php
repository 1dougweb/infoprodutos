@extends('membership.layout')

@section('title', 'Editar Banner')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-white">Editar Banner</h1>
        <a href="{{ route('admin.banners') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-white">Informações do Banner</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="title" class="form-label text-white">Título *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $banner->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-white">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $banner->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label text-white">Imagem</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <div class="form-text text-muted">
                                Deixe em branco para manter a imagem atual.
                                <br>Formatos aceitos: JPEG, PNG, JPG, GIF. Tamanho máximo: 2MB.
                                <br>Recomendado: Proporção 9:16 para melhor visualização.
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link" class="form-label text-white">Link (opcional)</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" 
                                   id="link" name="link" value="{{ old('link', $banner->link) }}" 
                                   placeholder="https://exemplo.com">
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label text-white">Ordem</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                           id="order" name="order" value="{{ old('order', $banner->order) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label text-white" for="is_active">
                                            Banner Ativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar Banner
                            </button>
                            <a href="{{ route('admin.banners') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 text-white">Imagem Atual</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img src="{{ Storage::url($banner->image_path) }}" 
                             alt="{{ $banner->title }}" 
                             class="img-fluid rounded" 
                             style="max-width: 100%; max-height: 400px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 