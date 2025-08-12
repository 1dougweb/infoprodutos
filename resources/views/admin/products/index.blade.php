@extends('membership.layout')

@php
use Illuminate\Support\Str;
@endphp

@section('title', 'Produtos')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-white">Gerenciar Produtos</h1>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Produto
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
                        <i class="bi bi-box-seam"></i> Lista de Produtos
                    </h5>
                </div>
                <div class="card-body">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Tipo</th>
                                        <th>Preço</th>
                                        <th>Ordem</th>
                                        <th>Status</th>
                                        <th>Arquivo</th>
                                        <th>Curso</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->id }}</td>
                                            <td>
                                                <strong>{{ $product->title }}</strong>
                                                @if($product->description)
                                                    <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($product->category) }}</span>
                                            </td>
                                            <td>
                                                @if($product->product_type === 'digital')
                                                    <span class="badge bg-warning">Produto Digital</span>
                                                @else
                                                    <span class="badge bg-primary">Curso</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->is_free)
                                                    <span class="badge bg-success">Gratuito</span>
                                                @else
                                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td>{{ $product->order }}</td>
                                            <td>
                                                @if($product->is_active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-danger">Inativo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->file_path)
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-file-earmark"></i> {{ $product->file_name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">Sem arquivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $modulesCount = $product->activeModules->count();
                                                    $lessonsCount = $product->getTotalLessonsCount();
                                                @endphp
                                                @if($modulesCount > 0 || $lessonsCount > 0)
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted">{{ $modulesCount }} módulos</small>
                                                        <small class="text-muted">{{ $lessonsCount }} aulas</small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">Sem curso</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#productModal{{ $product->id }}" 
                                                            title="Ver Detalhes">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <a href="{{ route('admin.products.edit', $product->id) }}" 
                                                       class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($product->product_type === 'course')
                                                        <a href="{{ route('admin.courses.modules', $product->id) }}" 
                                                           class="btn btn-sm btn-success" title="Gerenciar Curso">
                                                            <i class="bi bi-book"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-danger" title="Excluir"
                                                            onclick="confirmDelete('{{ route('admin.products.delete', $product->id) }}', '{{ $product->title }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-box-seam" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p class="text-muted">Nenhum produto encontrado.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Criar Primeiro Produto
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Detalhes dos Produtos -->
@foreach($products as $product)
<div class="modal fade centered-modal" id="productModal{{ $product->id }}" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Produto: {{ $product->title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <strong>ID:</strong><br>
                        #{{ $product->id }}
                    </div>
                    <div class="col-6">
                        <strong>Categoria:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($product->category) }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Preço:</strong><br>
                        @if($product->is_free)
                            <span class="badge bg-success">Gratuito</span>
                        @else
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        @endif
                    </div>
                    <div class="col-6">
                        <strong>Status:</strong><br>
                        @if($product->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Ordem:</strong><br>
                        {{ $product->order }}
                    </div>
                    <div class="col-6">
                        <strong>Data Criação:</strong><br>
                        {{ $product->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                @if($product->description)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Descrição:</strong><br>
                        <small class="text-muted">{{ $product->description }}</small>
                    </div>
                </div>
                @endif
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Arquivo:</strong><br>
                        @if($product->file_path)
                            <span class="badge bg-info">
                                <i class="bi bi-file-earmark"></i> {{ $product->file_name }}
                            </span>
                            <br><small class="text-muted">{{ number_format($product->file_size / 1024, 2) }} KB</small>
                        @else
                            <span class="badge bg-warning">Sem arquivo</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <strong>Curso:</strong><br>
                        @php
                            $modulesCount = $product->activeModules->count();
                            $lessonsCount = $product->getTotalLessonsCount();
                        @endphp
                        @if($modulesCount > 0 || $lessonsCount > 0)
                            <span class="badge bg-success">{{ $modulesCount }} módulos</span>
                            <br><span class="badge bg-info">{{ $lessonsCount }} aulas</span>
                        @else
                            <span class="badge bg-secondary">Sem curso</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="{{ route('admin.courses.modules', $product->id) }}" class="btn btn-success">
                    <i class="bi bi-book"></i> Gerenciar Curso
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection 