@extends('membership.layout')

@section('title', 'Criar Banner')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-white">Criar Banner</h1>
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
                    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label text-white">Título *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-white">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label text-white">Imagem *</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*" required>
                            <div class="form-text text-muted">
                                Formatos aceitos: JPEG, PNG, JPG, GIF. Tamanho máximo: 2MB.
                                <br>Recomendado: Proporção 9:16 para melhor visualização.
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link" class="form-label text-white">Link (opcional)</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" 
                                   id="link" name="link" value="{{ old('link') }}" 
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
                                           id="order" name="order" value="{{ old('order', 0) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                                        <label class="form-check-label text-white" for="is_active">
                                            Banner Ativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Criar Banner
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
                    <h5 class="mb-0 text-white">Preview</h5>
                </div>
                <div class="card-body">
                    <div id="imagePreview" class="text-center" style="display: none;">
                        <img id="previewImage" src="" alt="Preview" 
                             class="img-fluid rounded" 
                             style="max-width: 100%; max-height: 400px; object-fit: cover;">
                    </div>
                    <div id="noImage" class="text-center text-muted">
                        <i class="bi bi-image fs-1"></i>
                        <p class="mt-2">Selecione uma imagem para ver o preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const noImage = document.getElementById('noImage');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
            noImage.style.display = 'none';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        noImage.style.display = 'block';
    }
});
</script>
@endsection 