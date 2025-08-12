<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConfigureMercadoPago extends Command
{
    protected $signature = 'mercadopago:configure';
    protected $description = 'Configure Mercado Pago credentials interactively';

    public function handle()
    {
        $this->info('=== Configuração do Mercado Pago ===');
        $this->info('');
        
        $this->info('Para obter suas credenciais:');
        $this->info('1. Acesse: https://www.mercadopago.com.br/developers/panel/credentials');
        $this->info('2. Copie o "Access Token" de produção');
        $this->info('3. Cole aqui quando solicitado');
        $this->info('');
        
        $accessToken = $this->ask('Digite seu Access Token do Mercado Pago:');
        
        if (empty($accessToken)) {
            $this->error('Access Token não pode estar vazio!');
            return 1;
        }
        
        // Salvar no banco de dados usando o modelo Setting
        \App\Models\Setting::set('mercadopago_access_token', $accessToken);
        
        $this->info('');
        $this->info('✅ Credenciais configuradas com sucesso!');
        $this->info('Access Token: ' . $accessToken);
        $this->info('Token: ' . substr($accessToken, 0, 10) . '...');
        $this->info('');
        $this->info('Agora execute: php artisan config:clear');
        $this->info('E depois: php artisan mercadopago:test-sdk');
        
        return 0;
    }
} 