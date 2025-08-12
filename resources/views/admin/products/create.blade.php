@extends('membership.layout')

@section('title', 'Novo Produto')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-white">Novo Produto</h1>
            <a href="{{ route('admin.products') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-circle"></i> Criar Novo Produto
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Título *</label>
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
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Categoria *</label>
                                            <select class="form-control @error('category') is-invalid @enderror" 
                                                    id="category" name="category" required>
                                                <option value="">Selecione...</option>
                                                <option value="module" {{ old('category') == 'module' ? 'selected' : '' }}>Módulo</option>
                                                <option value="bonus" {{ old('category') == 'bonus' ? 'selected' : '' }}>Bônus</option>
                                                <option value="contact" {{ old('category') == 'contact' ? 'selected' : '' }}>Contato</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="product_type" class="form-label">Tipo de Produto *</label>
                                            <select class="form-control @error('product_type') is-invalid @enderror" 
                                                    id="product_type" name="product_type" required>
                                                <option value="">Selecione...</option>
                                                <option value="course" {{ old('product_type', 'course') == 'course' ? 'selected' : '' }}>Curso</option>
                                                <option value="digital" {{ old('product_type', 'course') == 'digital' ? 'selected' : '' }}>Produto Digital</option>
                                            </select>
                                            @error('product_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="order" class="form-label">Ordem *</label>
                                            <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                                   id="order" name="order" value="{{ old('order', 0) }}" min="0" required>
                                            @error('order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Preço *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price', 0) }}" 
                                               step="0.01" min="0" required>
                                        <input type="hidden" id="price_hidden" name="price" value="{{ old('price', 0) }}">
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_free" name="is_free" 
                                               value="1" {{ old('is_free') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_free">
                                            Produto Gratuito
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Produto Ativo
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagem do Produto</label>
                                    <div class="file-upload-area" onclick="document.getElementById('image').click()">
                                        <div class="file-upload-icon">
                                            <i class="bi bi-image"></i>
                                        </div>
                                        <div class="file-upload-text">
                                            Clique para selecionar uma imagem ou arraste aqui
                                        </div>
                                        <div class="file-upload-info">
                                            Formatos aceitos: JPG, PNG, GIF, WEBP (máx. 5MB)
                                        </div>
                                    </div>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" 
                                           style="display: none;" onchange="updateImageInfo(this)">
                                    <div id="image-info" style="display: none;" class="current-file">
                                        <div class="current-file-name" id="image-name"></div>
                                        <div class="current-file-size" id="image-size"></div>
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="file" class="form-label">Arquivo do Produto</label>
                                    <div class="file-upload-area" onclick="document.getElementById('file').click()">
                                        <div class="file-upload-icon">
                                            <i class="bi bi-cloud-upload"></i>
                                        </div>
                                        <div class="file-upload-text">
                                            Clique para selecionar um arquivo ou arraste aqui
                                        </div>
                                        <div class="file-upload-info">
                                            Formatos aceitos: PDF, ZIP, RAR, DOC, PSD, AI, EPS (máx. 10MB)
                                        </div>
                                    </div>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".pdf,.zip,.rar,.doc,.docx,.psd,.ai,.eps" 
                                           style="display: none;" onchange="updateFileInfo(this)">
                                    <div id="file-info" style="display: none;" class="current-file">
                                        <div class="current-file-name" id="file-name"></div>
                                        <div class="current-file-size" id="file-size"></div>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.products') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salvar Produto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isFreeCheckbox = document.getElementById('is_free');
    const priceInput = document.getElementById('price');
    const form = document.querySelector('form');
    
    function togglePriceField() {
        const priceHidden = document.getElementById('price_hidden');
        
        if (isFreeCheckbox.checked) {
            priceInput.value = '0';
            priceHidden.value = '0';
            priceInput.disabled = true;
            priceInput.style.opacity = '0.5';
        } else {
            priceInput.disabled = false;
            priceInput.style.opacity = '1';
        }
    }
    
    // Sincronizar campos de preço
    priceInput.addEventListener('input', function() {
        document.getElementById('price_hidden').value = this.value;
    });
    
    isFreeCheckbox.addEventListener('change', togglePriceField);
    
    // Trigger on page load
    togglePriceField();
    


    // Drag and drop functionality
    const uploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('file');

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        updateFileInfo(fileInput);
    });
});

function updateFileInfo(input) {
    const file = input.files[0];
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    if (file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.style.display = 'block';
    } else {
        fileInfo.style.display = 'none';
    }
}

function updateImageInfo(input) {
    const file = input.files[0];
    const imageInfo = document.getElementById('image-info');
    const imageName = document.getElementById('image-name');
    const imageSize = document.getElementById('image-size');

    if (file) {
        imageName.textContent = file.name;
        imageSize.textContent = formatFileSize(file.size);
        imageInfo.style.display = 'block';
    } else {
        imageInfo.style.display = 'none';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endsection 