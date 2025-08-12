<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckAuthStatus extends Command
{
    protected $signature = 'auth:check';
    protected $description = 'Check authentication status';

    public function handle()
    {
        $this->info('=== Status da Autenticação ===');
        
        // Verificar se há sessão ativa
        $this->info('Sessão ID: ' . (Session::getId() ?: 'Nenhuma'));
        
        // Verificar se o usuário está logado
        $this->info('Usuário logado: ' . (Auth::check() ? 'Sim' : 'Não'));
        
        if (Auth::check()) {
            $user = Auth::user();
            $this->info('ID do usuário: ' . $user->id);
            $this->info('Email: ' . $user->email);
            $this->info('Nome: ' . $user->name);
            $this->info('Admin: ' . ($user->isAdmin() ? 'Sim' : 'Não'));
        }
        
        // Verificar configuração de sessão
        $this->info('Driver de sessão: ' . config('session.driver'));
        $this->info('Lifetime da sessão: ' . config('session.lifetime') . ' minutos');
        
        return 0;
    }
} 