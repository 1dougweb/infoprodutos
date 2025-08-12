<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DigitalProduct;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class TestCreditCard extends Command
{
    protected $signature = 'creditcard:test {product_id} {order_id}';
    protected $description = 'Testa o fluxo completo do cartão de crédito';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $orderId = $this->argument('order_id');

        $this->info("=== TESTE DO CARTÃO DE CRÉDITO ===");
        $this->info("Produto ID: {$productId}");
        $this->info("Pedido ID: {$orderId}");

        try {
            // Buscar produto e pedido
            $product = DigitalProduct::findOrFail($productId);
            $order = Order::findOrFail($orderId);

            $this->info("Produto: {$product->title} - R$ {$product->price}");
            $this->info("Pedido: {$order->id} - Status: {$order->status}");

            // Verificar token do Mercado Pago
            $accessToken = Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                $this->error('Token de acesso do Mercado Pago não configurado');
                return 1;
            }

            $this->info('Token de acesso configurado com sucesso');

            // Simular dados de cartão de crédito
            $cardData = [
                'product_id' => $productId,
                'order_id' => $orderId,
                'payment_method' => 'credit_card',
                'card_token' => 'test_token_' . time() . '_' . rand(1000, 9999), // Token único
                'card_brand' => 'visa',
                'installments' => 1,
                'card_holder_name' => 'Test User',
                'card_holder_doc' => '11144477735', // CPF válido de teste
                'payer' => [
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'last_name' => 'User'
                ]
            ];

            $this->info('Dados do cartão simulados:');
            $this->line(json_encode($cardData, JSON_PRETTY_PRINT));

            // Fazer requisição para a API
            $client = new \GuzzleHttp\Client();
            
            $this->info('Fazendo requisição para criar ordem...');
            
            $response = $client->post('http://127.0.0.1:8000/payment/create-order', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $cardData,
                'timeout' => 30
            ]);

            $responseData = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 200) {
                $this->info('✅ Resposta da API:');
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
            } else {
                $this->error('❌ Erro na API:');
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
            }

            $this->info('=== TESTE CONCLUÍDO ===');
            return 0;

        } catch (\Exception $e) {
            $this->error('Erro durante o teste: ' . $e->getMessage());
            Log::error('Erro no teste de cartão de crédito: ' . $e->getMessage());
            return 1;
        }
    }
}
