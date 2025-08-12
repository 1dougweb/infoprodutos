<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class TestMercadoPagoConnection extends Command
{
    protected $signature = 'test:mercadopago';
    protected $description = 'Test Mercado Pago connection';

    public function handle()
    {
        try {
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            
            if (!$accessToken) {
                $this->error('Access Token não configurado');
                return 1;
            }
            
            $this->info('Access Token configurado: ' . substr($accessToken, 0, 10) . '...');
            
            MercadoPagoConfig::setAccessToken($accessToken);
            
            // Teste simples de conectividade usando cURL direto
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payment_methods');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new \Exception('Erro cURL: ' . $curlError);
            }
            
            if ($httpCode !== 200) {
                throw new \Exception('HTTP Code: ' . $httpCode . ' - Response: ' . $response);
            }
            
            $this->info('Conexão com Mercado Pago OK!');
            $this->info('HTTP Code: ' . $httpCode);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Erro na conexão: ' . $e->getMessage());
            return 1;
        }
    }
} 