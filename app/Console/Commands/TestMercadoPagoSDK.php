<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use MP;

class TestMercadoPagoSDK extends Command
{
    protected $signature = 'mercadopago:test-sdk';
    protected $description = 'Test Mercado Pago SDK connection';

    public function handle()
    {
        try {
            $this->info('Testando conexão com Mercado Pago usando SDK Laravel...');

            // Verificar se as configurações estão definidas
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (!$accessToken) {
                $this->error('❌ Token do Mercado Pago não configurado!');
                $this->info('Configure através do painel administrativo ou use: php artisan mercadopago:set-credentials');
                return 1;
            }

            $this->info('Token configurado: ' . substr($accessToken, 0, 10) . '...');

            // Teste simples - criar uma preferência de teste
            $preference_data = array(
                "items" => array(
                    array(
                        "title" => "Teste SDK Laravel",
                        "quantity" => 1,
                        "currency_id" => "BRL",
                        "unit_price" => 1.00
                    )
                )
            );

            $preference = MP::create_preference($preference_data);

            if (isset($preference['response']['id'])) {
                $this->info('✅ SDK Laravel funcionando!');
                $this->info('Preference ID: ' . $preference['response']['id']);
                $this->info('Init Point: ' . $preference['response']['init_point']);
                return 0;
            } else {
                $this->error('❌ Erro na resposta do SDK');
                $this->error('Resposta: ' . json_encode($preference));
                return 1;
            }

        } catch (Exception $e) {
            $this->error('❌ Erro no SDK: ' . $e->getMessage());
            return 1;
        }
    }
} 