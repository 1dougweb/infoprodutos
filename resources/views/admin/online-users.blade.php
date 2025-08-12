@extends('membership.layout')

@section('title', 'Usuários Online')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-white">Usuários Online</h1>
                <p class="text-muted mb-0">Monitoramento em tempo real</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="online-indicator">
                    <div class="pulse-dot"></div>
                    <span class="ms-2">{{ $onlineUsers->count() }} usuários online</span>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Cards do topo estilo dashboard -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Online Agora
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $onlineUsers->where('status', 'online')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cup-hot fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ausente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $onlineUsers->where('status', 'away')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Admins
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $onlineUsers->where('is_admin', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shield-check fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Usuários
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $onlineUsers->where('is_admin', false)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Lista de Usuários -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people-fill"></i> Usuários Ativos
                    </h5>
                </div>
                <div class="card-body">
                    @if($onlineUsers->count() > 0)
                        <div class="users-grid">
                            @foreach($onlineUsers as $user)
                                <div class="user-card">
                                    <div class="user-card-header">
                                        <div class="user-avatar {{ $user['is_admin'] ? 'admin' : '' }}">
                                            {{ $user['avatar'] }}
                                        </div>
                                        <div class="user-status">
                                            <div class="status-dot {{ $user['status'] }}"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info">
                                        <h6 class="user-name">{{ $user['name'] }}</h6>
                                        <p class="user-email">{{ $user['email'] }}</p>
                                        
                                        <div class="user-details">
                                            <div class="detail-item">
                                                <i class="bi bi-geo-alt"></i>
                                                <span>{{ $user['current_page'] }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="bi bi-clock"></i>
                                                <span>{{ $user['online_duration'] }}</span>
                                            </div>
                                        </div>
                                        
                                        @if($user['is_admin'])
                                            <div class="admin-badge">
                                                <i class="bi bi-shield-check"></i>
                                                Administrador
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="user-actions">
                                        <button class="btn btn-sm btn-outline-primary" title="Ver perfil">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" title="Enviar mensagem">
                                            <i class="bi bi-chat"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4>Nenhum usuário online</h4>
                            <p>Não há usuários ativos no momento.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Indicador Online */
.online-indicator {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.3);
    border-radius: 20px;
    color: #28a745;
    font-weight: 500;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

/* Grid de Usuários */
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.user-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    /* Removida a borda lateral esquerda */
}

.user-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    border-color: var(--primary-blue);
}

/* Removido: .user-card.online, .user-card.away { border-left: ... } */

.user-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--primary-blue), var(--secondary-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
}

.user-avatar.admin {
    background: linear-gradient(45deg, #dc3545, #c82333);
}

.user-status {
    position: relative;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid var(--card-bg);
}

.status-dot.online {
    background-color: #28a745;
    animation: pulse 2s infinite;
}

.status-dot.away {
    background-color: #ffc107;
}

.user-info {
    margin-bottom: 15px;
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: var(--text-light);
}

.user-email {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0 0 10px 0;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-muted);
}

.detail-item i {
    color: var(--primary-blue);
    font-size: 14px;
}

.admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    margin-top: 8px;
}

.user-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.user-actions .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

/* Estado Vazio */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 20px;
}

.empty-state h4 {
    color: var(--text-light);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--text-muted);
    margin: 0;
}

/* Responsividade */
@media (max-width: 768px) {
    .users-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .user-card {
        padding: 15px;
    }
}

@media (max-width: 576px) {
    .user-card {
        padding: 12px;
    }
}

.icon-primary { color: #007bff !important; }
.icon-warning { color: #ffc107 !important; }
.icon-danger  { color: #dc3545 !important; }
.icon-info    { color: #17a2b8 !important; }
</style>

<script>
// Auto-refresh a cada 30 segundos
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endsection 