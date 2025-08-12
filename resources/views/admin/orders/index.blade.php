@extends('membership.layout')

@section('title', 'Pedidos')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-white">Gerenciar Pedidos</h1>
        </div>
    </div>



    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cart3"></i> Lista de Pedidos
                    </h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuário</th>
                                        <th>Produto</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Método de Pagamento</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>
                                                <strong>{{ $order->user->name }}</strong>
                                                <br><small class="text-muted">{{ $order->user->email }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $order->digitalProduct->title }}</strong>
                                                <br><small class="text-muted">{{ ucfirst($order->digitalProduct->category) }}</small>
                                            </td>
                                            <td>R$ {{ number_format($order->amount, 2, ',', '.') }}</td>
                                            <td>
                                                @switch($order->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-clock"></i> Pendente
                                                        </span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> Aprovado
                                                        </span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle"></i> Cancelado
                                                        </span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-exclamation-triangle"></i> Falhou
                                                        </span>
                                                        @break
                                                    @case('refunded')
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-arrow-return-left"></i> Reembolsado
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark">
                                                            <i class="bi bi-question-circle"></i> {{ ucfirst($order->status) }}
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($order->payment_method)
                                                    @switch(strtolower($order->payment_method))
                                                        @case('credit_card')
                                                        @case('debit_card')
                                                            <span class="badge bg-primary">
                                                                <i class="bi bi-credit-card"></i> Cartão
                                                            </span>
                                                            @break
                                                        @case('pix')
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-qr-code"></i> PIX
                                                            </span>
                                                            @break
                                                        @case('boleto')
                                                            <span class="badge bg-info">
                                                                <i class="bi bi-receipt"></i> Boleto
                                                            </span>
                                                            @break
                                                        @case('transfer')
                                                            <span class="badge bg-warning">
                                                                <i class="bi bi-bank"></i> Transferência
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">
                                                                <i class="bi bi-question-circle"></i> {{ ucfirst($order->payment_method) }}
                                                            </span>
                                                    @endswitch
                                                @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bi bi-dash-circle"></i> Não definido
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#orderModal{{ $order->id }}" 
                                                            title="Ver Detalhes">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="openStatusModal({{ $order->id }}, '{{ $order->status }}', '{{ $order->user->name }}', '{{ $order->digitalProduct->title }}')"
                                                            title="Editar Status">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteOrder({{ $order->id }})"
                                                            title="Excluir Pedido">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            @if($orders->hasPages())
                                <nav aria-label="Paginação de pedidos">
                                    <ul class="pagination">
                                        {{-- Botão Anterior --}}
                                        @if($orders->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">Anterior</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $orders->previousPageUrl() }}">Anterior</a>
                                            </li>
                                        @endif

                                        {{-- Links das páginas --}}
                                        @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                            @if($page == $orders->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Botão Próximo --}}
                                        @if($orders->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $orders->nextPageUrl() }}">Próximo</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">Próximo</span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-cart3" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p class="text-muted">Nenhum pedido encontrado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Detalhes -->
@foreach($orders as $order)
<div class="modal fade centered-modal" id="orderModal{{ $order->id }}" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido #{{ $order->id }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <strong>Usuário:</strong><br>
                        {{ $order->user->name }}<br>
                        <small class="text-muted">{{ $order->user->email }}</small>
                    </div>
                    <div class="col-6">
                        <strong>Produto:</strong><br>
                        {{ $order->digitalProduct->title }}<br>
                        <small class="text-muted">{{ ucfirst($order->digitalProduct->category) }}</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Valor:</strong><br>
                        R$ {{ number_format($order->amount, 2, ',', '.') }}
                    </div>
                    <div class="col-6">
                        <strong>Status:</strong><br>
                        @switch($order->status)
                            @case('approved')
                                <span class="badge bg-success">Aprovado</span>
                                @break
                            @case('pending')
                                <span class="badge bg-warning">Pendente</span>
                                @break
                            @case('rejected')
                                <span class="badge bg-danger">Rejeitado</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                        @endswitch
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Data:</strong><br>
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="col-6">
                        <strong>Método:</strong><br>
                        {{ $order->payment_method ?? 'N/A' }}
                    </div>
                </div>
                @if($order->mercadopago_payment_id)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Mercado Pago ID:</strong><br>
                        <small class="text-muted">{{ $order->mercadopago_payment_id }}</small>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modal para editar status -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Editar Status do Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Pedido:</strong> <span id="orderInfo"></span>
                </div>
                <div class="mb-3">
                    <label for="newStatus" class="form-label">Novo Status:</label>
                    <select class="form-select" id="newStatus">
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovado</option>
                        <option value="cancelled">Cancelado</option>
                        <option value="failed">Falhou</option>
                        <option value="refunded">Reembolsado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="statusReason" class="form-label">Motivo (opcional):</label>
                    <textarea class="form-control" id="statusReason" rows="3" placeholder="Digite o motivo da alteração..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para gerenciamento de pedidos -->
<style>
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: var(--primary-blue);
    background-color: var(--card-bg);
    border-color: rgba(255, 255, 255, 0.1);
}

.pagination .page-link:hover {
    color: white;
    background-color: var(--primary-blue);
    border-color: var(--primary-blue);
}

.pagination .page-item.active .page-link {
    background-color: var(--primary-blue);
    border-color: var(--primary-blue);
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: var(--text-muted);
    background-color: var(--card-bg);
    border-color: rgba(255, 255, 255, 0.1);
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.status-select {
    min-width: 120px;
}

.badge {
    font-size: 0.75rem;
}
</style>

<script>
function deleteOrder(orderId) {
    if (!confirm('Deseja realmente excluir este pedido?')) {
        return;
    }
    
    fetch(`/admin/orders/${orderId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Erro ao excluir pedido.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar requisição.');
    });
}

// Variáveis globais para o modal
let currentOrderId = null;
let currentOrderStatus = null;

// Abrir modal de edição de status
function openStatusModal(orderId, currentStatus, userName, productName) {
    currentOrderId = orderId;
    currentOrderStatus = currentStatus;
    
    // Preencher informações do pedido
    document.getElementById('orderInfo').textContent = `#${orderId} - ${userName} - ${productName}`;
    
    // Definir status atual no select
    document.getElementById('newStatus').value = currentStatus;
    
    // Limpar campo de motivo
    document.getElementById('statusReason').value = '';
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Atualizar status do pedido via modal
function updateOrderStatus() {
    const newStatus = document.getElementById('newStatus').value;
    const reason = document.getElementById('statusReason').value;
    
    if (!currentOrderId) {
        alert('Erro: ID do pedido não encontrado.');
        return;
    }
    
    if (newStatus === currentOrderStatus) {
        alert('O status selecionado é o mesmo do atual.');
        return;
    }
    
    fetch(`/admin/orders/${currentOrderId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: newStatus,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
            modal.hide();
            
            // Recarregar página para mostrar as alterações
            location.reload();
        } else {
            alert('Erro ao atualizar status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar requisição.');
    });
}
</script>
@endsection 