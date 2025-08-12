<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetMercadoPagoCredentials extends Command
{
    protected $signature = 'mercadopago:set-credentials {access_token} {public_key?}';
    protected $description = 'Set Mercado Pago credentials';

    public function handle()
    {
        $accessToken = $this->argument('access_token');
        $publicKey = $this->argument('public_key');

        // Salvar no banco de dados usando o modelo Setting
        \App\Models\Setting::set('mercadopago_access_token', $accessToken);
        
        if ($publicKey) {
            \App\Models\Setting::set('mercadopago_public_key', $publicKey);
        }

        $this->info('Credenciais do Mercado Pago configuradas com sucesso!');
        $this->info('Access Token: ' . substr($accessToken, 0, 10) . '...');
        if ($publicKey) {
            $this->info('Public Key: ' . substr($publicKey, 0, 10) . '...');
        }
        $this->info('');
        $this->info('IMPORTANTE: Use tokens reais do Mercado Pago para produção!');
        $this->info('Para obter tokens válidos, acesse: https://www.mercadopago.com.br/developers');

        return 0;
    }
} 