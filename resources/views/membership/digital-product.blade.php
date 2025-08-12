@extends('membership.layout')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', $product->title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h3 mb-0 text-white">{{ $product->title }}</h1>
                </div>
                <div class="card-body">
                    @if($product->image)
                        <div class="text-center mb-4">
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" 
                                 class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    @endif

                    @if($product->description)
                        <div class="mb-4">
                            <h5>Descrição</h5>
                            <p class="text-muted">{{ $product->description }}</p>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Produto Digital:</strong> Este é um produto digital que você pode baixar diretamente.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-download"></i> Download
                    </h5>
                </div>
                <div class="card-body">
                    @if($product->file_path)
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-file-earmark me-2"></i>
                                <strong>{{ $product->file_name }}</strong>
                            </div>
                            <small class="text-muted">
                                Tamanho: {{ number_format($product->file_size / 1024, 2) }} KB
                            </small>
                        </div>

                        <a href="{{ route('membership.digital.download', $product->id) }}" 
                           class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-download me-2"></i>
                            Baixar Arquivo
                        </a>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Clique no botão acima para baixar o arquivo do produto.
                            </small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Este produto ainda não possui arquivo para download.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Informações
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Categoria</small>
                            <div class="fw-bold">{{ ucfirst($product->category) }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Tipo</small>
                            <div class="fw-bold">Produto Digital</div>
                        </div>
                    </div>
                    
                    @if($product->price > 0)
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Preço</small>
                                <div class="fw-bold text-success">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    @else
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Preço</small>
                                <div class="fw-bold text-success">Gratuito</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 