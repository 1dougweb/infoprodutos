@extends('membership.layout')

@section('title', 'Meu Perfil')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle"></i>
                    Informações do Perfil
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('membership.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Deixe em branco para manter a atual">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirme a nova senha">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i>
                            Salvar Alterações
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
                    Informações da Conta
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Membro desde:</strong>
                    <p class="text-muted">{{ $user->created_at->format('d/m/Y') }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Último acesso:</strong>
                    <p class="text-muted">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="mb-3">
                    <strong>Produtos adquiridos:</strong>
                    <p class="text-muted">{{ $user->purchases->count() }}</p>
                </div>

                <div class="mb-3">
                    <strong>Status da conta:</strong>
                    <span class="badge bg-success">Ativa</span>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check"></i>
                    Segurança
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-warning" type="button">
                        <i class="bi bi-key"></i>
                        Alterar Senha
                    </button>
                    <button class="btn btn-outline-info" type="button">
                        <i class="bi bi-envelope"></i>
                        Verificar E-mail
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i>
                    Atividade Recente
                </h5>
            </div>
            <div class="card-body">
                @if($user->purchases->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Data de Compra</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->purchases->take(5) as $purchase)
                                    <tr>
                                        <td>{{ $purchase->digitalProduct->title }}</td>
                                        <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-success">Concluído</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('membership.download', $purchase->digital_product_id) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-download"></i>
                                                Baixar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-muted);"></i>
                        <p class="text-muted mt-3">Nenhuma atividade encontrada.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 