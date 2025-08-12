@extends('membership.layout')

@section('title', 'Usuários')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-white">Gerenciar Usuários</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people-fill"></i> Lista de Usuários
                    </h5>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Compras</th>
                                        <th>Pedidos</th>
                                        <th>Data Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <strong>{{ $user->name }}</strong>
                                                @if($user->isAdmin())
                                                    <br><span class="badge bg-danger">Admin</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->isAdmin())
                                                    <span class="badge bg-danger">Administrador</span>
                                                @else
                                                    <span class="badge bg-info">Usuário</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $user->purchases_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ $user->orders_count }}</span>
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Ações do usuário">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#userModal{{ $user->id }}" 
                                                            title="Ver Detalhes">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    @if(!$user->isAdmin())
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#makeAdminModal{{ $user->id }}" 
                                                                title="Tornar Admin">
                                                            <i class="bi bi-shield-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteUserModal{{ $user->id }}" 
                                                                title="Excluir Usuário">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-people-fill" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p class="text-muted">Nenhum usuário encontrado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Detalhes -->
@foreach($users as $user)
<div class="modal fade centered-modal" id="userModal{{ $user->id }}" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Usuário: {{ $user->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <strong>Nome:</strong><br>
                        {{ $user->name }}
                    </div>
                    <div class="col-6">
                        <strong>Email:</strong><br>
                        {{ $user->email }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Tipo:</strong><br>
                        @if($user->isAdmin())
                            <span class="badge bg-danger">Administrador</span>
                        @else
                            <span class="badge bg-info">Usuário</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <strong>Data Cadastro:</strong><br>
                        {{ $user->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Total de Compras:</strong><br>
                        <span class="badge bg-success">{{ $user->purchases_count }}</span>
                    </div>
                    <div class="col-6">
                        <strong>Total de Pedidos:</strong><br>
                        <span class="badge bg-warning">{{ $user->orders_count }}</span>
                    </div>
                </div>
                @if($user->purchases_count > 0)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Produtos Comprados:</strong><br>
                        <ul class="list-unstyled">
                            @foreach($user->purchases as $purchase)
                                <li><i class="bi bi-check-circle-fill text-success"></i> {{ $purchase->digitalProduct->title }}</li>
                            @endforeach
                        </ul>
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

<!-- Modais de Tornar Admin -->
@foreach($users as $user)
    @if(!$user->isAdmin())
    <div class="modal fade centered-modal" id="makeAdminModal{{ $user->id }}" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-shield-check text-warning"></i>
                        Tornar Administrador
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja tornar <strong>{{ $user->name }}</strong> um administrador?</p>
                    <p class="text-muted">Esta ação dará acesso total ao painel administrativo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="#" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-check"></i>
                            Tornar Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<!-- Modais de Exclusão -->
@foreach($users as $user)
    @if(!$user->isAdmin())
    <div class="modal fade centered-modal" id="deleteUserModal{{ $user->id }}" tabindex="-1" style="backdrop-filter: blur(10px); background-color: rgba(0, 0, 0, 0.7);">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        Excluir Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                    </div>
                    <p>Tem certeza que deseja excluir o usuário <strong>{{ $user->name }}</strong>?</p>
                    <ul class="text-muted">
                        <li>O usuário será removido permanentemente</li>
                        <li>Todos os dados associados serão perdidos</li>
                        <li>Esta ação não pode ser revertida</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="#" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash-fill"></i>
                            Excluir Usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection 