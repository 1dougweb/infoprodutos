<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class SetProductionToken extends Command
{
    protected $signature = 'mercadopago:set-production-token {token}';
    protected $description = 'Set Mercado Pago production access token';

    public function handle()
    {
        $token = $this->argument('token');
        
        if (empty($token)) {
            $this->error('Token não fornecido');
            return 1;
        }
        
        // Validar formato do token (deve começar com APP)
        if (!str_starts_with($token, 'APP')) {
            $this->error('Token de produção deve começar com APP');
            return 1;
        }
        
        Setting::set('mercadopago_access_token', $token);
        
        $this->info('Token de produção configurado com sucesso!');
        $this->info('Token: ' . substr($token, 0, 10) . '...');
        
        return 0;
    }
} 