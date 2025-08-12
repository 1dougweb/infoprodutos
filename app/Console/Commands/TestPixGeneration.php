<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DigitalProduct;
use App\Models\Order;
use App\Models\User;

class TestPixGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pix:test {--product-id=1} {--user-id=1} {--amount=99.90}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a geração de PIX via API do Mercado Pago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TESTANDO GERAÇÃO DE PIX ===');
        
        $productId = $this->option('product-id');
        $userId = $this->option('user-id');
        $amount = $this->option('amount');
        
        try {
            // Buscar produto
            $product = DigitalProduct::find($productId);
            if (!$product) {
                $this->error("Produto com ID {$productId} não encontrado");
                return 1;
            }
            
            // Buscar usuário
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuário com ID {$userId} não encontrado");
                return 1;
            }
            
            $this->info("Produto: {$product->title} - R$ {$product->price}");
            $this->info("Usuário: {$user->name} ({$user->email})");
            $this->info("Valor: R$ {$amount}");
            
            // Criar pedido de teste
            $order = Order::create([
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'amount' => $amount,
                'status' => 'pending'
            ]);
            
            $this->info("Pedido criado: {$order->id}");
            
            // Testar geração de PIX
            $this->info("\n1. Testando geração de PIX via API de Payments...");
            $pixResult = $this->testPixGeneration($product, $order, $user);
            
            if ($pixResult['success']) {
                $this->info("✅ PIX gerado com sucesso!");
                $this->info("Payment ID: {$pixResult['payment_id']}");
                $this->info("Status: {$pixResult['status']}");
                
                if (isset($pixResult['pix_data'])) {
                    $this->info("QR Code: " . ($pixResult['pix_data']['qr_code'] ?: 'não disponível'));
                    $this->info("QR Code Base64: " . ($pixResult['pix_data']['qr_code_base64'] ? 'disponível' : 'não disponível'));
                    $this->info("Ticket URL: " . ($pixResult['pix_data']['ticket_url'] ?: 'não disponível'));
                }
            } else {
                $this->error("❌ Erro ao gerar PIX: {$pixResult['error']}");
                if (isset($pixResult['details'])) {
                    $this->error("Detalhes: " . json_encode($pixResult['details'], JSON_PRETTY_PRINT));
                }
                if (isset($pixResult['suggestion'])) {
                    $this->warn("Sugestão: {$pixResult['suggestion']}");
                }
            }
            
            // Testar API de Orders (para comparação)
            $this->info("\n2. Testando API de Orders para comparação...");
            $orderResult = $this->testOrderCreation($product, $order, $user);
            
            if ($orderResult['success']) {
                $this->info("✅ Ordem criada com sucesso!");
                $this->info("Order ID: {$orderResult['order_id']}");
                $this->info("Status: {$orderResult['status']}");
            } else {
                $this->error("❌ Erro ao criar ordem: {$orderResult['error']}");
            }
            
            // Limpar pedido de teste
            $order->delete();
            $this->info("\nPedido de teste removido");
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            Log::error('Erro no teste de PIX: ' . $e->getMessage());
            return 1;
        }
        
        $this->info("\n=== TESTE CONCLUÍDO ===");
        return 0;
    }
    
    /**
     * Testa a geração de PIX via API de Payments
     */
    private function testPixGeneration($product, $order, $user)
    {
        try {
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                return [
                    'success' => false,
                    'error' => 'Token de acesso não configurado'
                ];
            }
            
            $webhookUrl = url('/api/webhooks/mercadopago');
            
            $payload = [
                'description' => 'Teste PIX - ' . $product->title,
                'external_reference' => 'test_order_' . $order->id,
                'payer' => [
                    'email' => $user->email,
                    'first_name' => $user->name ?? 'Teste',
                    'entity_type' => 'individual'
                ],
                'payment_method_id' => 'pix',
                'transaction_amount' => (float) $order->amount,
                'notification_url' => $webhookUrl
            ];
            
            $this->info("Payload para API de Payments:");
            $this->line(json_encode($payload, JSON_PRETTY_PRINT));
            
            $idempotencyKey = md5('test_pix_' . $order->id . '_' . time());
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey
                ])
                ->post('https://api.mercadopago.com/v1/payments', $payload);
            
            $this->info("Status da resposta: {$response->status()}");
            $this->info("Headers da resposta:");
            foreach ($response->headers() as $header => $values) {
                $this->line("  {$header}: " . implode(', ', $values));
            }
            
            if ($response->successful()) {
                $responseData = $response->json();
                $this->info("Resposta da API:");
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
                
                if (isset($responseData['id'])) {
                    return [
                        'success' => true,
                        'payment_id' => $responseData['id'],
                        'status' => $responseData['status'],
                        'pix_data' => $this->extractPixDataFromResponse($responseData)
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Resposta sem ID de pagamento',
                        'details' => $responseData
                    ];
                }
            } else {
                $this->error("Erro HTTP: {$response->status()}");
                $this->error("Body da resposta: " . $response->body());
                
                return [
                    'success' => false,
                    'error' => 'Erro HTTP: ' . $response->status(),
                    'details' => json_decode($response->body(), true),
                    'status_code' => $response->status(),
                    'suggestion' => $this->getErrorSuggestion($response->status())
                ];
            }
            
        } catch (\Exception $e) {
            $this->error("Exceção: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ];
        }
    }
    
    /**
     * Testa a criação de ordem via API de Orders
     */
    private function testOrderCreation($product, $order, $user)
    {
        try {
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                return [
                    'success' => false,
                    'error' => 'Token de acesso não configurado'
                ];
            }
            
            $webhookUrl = url('/api/webhooks/mercadopago');
            
            $orderData = [
                "type" => "online",
                "external_reference" => "test_order_" . $order->id,
                "total_amount" => (string) number_format($order->amount, 2, '.', ''),
                "description" => "Teste - " . $product->title,
                "notification_url" => $webhookUrl,
                "payer" => [
                    "email" => $user->email,
                    "entity_type" => "individual",
                    "first_name" => $user->name ?? 'Teste'
                ]
            ];
            
            $this->info("Payload para API de Orders:");
            $this->line(json_encode($orderData, JSON_PRETTY_PRINT));
            
            $idempotencyKey = uniqid('test_order_') . '_' . $order->id;
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey
                ])
                ->post('https://api.mercadopago.com/v1/orders', $orderData);
            
            $this->info("Status da resposta: {$response->status()}");
            
            if ($response->successful()) {
                $responseData = $response->json();
                $this->info("Resposta da API:");
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
                
                if (isset($responseData['id'])) {
                    return [
                        'success' => true,
                        'order_id' => $responseData['id'],
                        'status' => $responseData['status']
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Resposta sem ID de ordem',
                        'details' => $responseData
                    ];
                }
            } else {
                $this->error("Erro HTTP: {$response->status()}");
                $this->error("Body da resposta: " . $response->body());
                
                return [
                    'success' => false,
                    'error' => 'Erro HTTP: ' . $response->status(),
                    'details' => json_decode($response->body(), true)
                ];
            }
            
        } catch (\Exception $e) {
            $this->error("Exceção: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ];
        }
    }
    
    /**
     * Extrai dados do PIX da resposta
     */
    private function extractPixDataFromResponse($responseData)
    {
        $pixData = [
            'payment_id' => $responseData['id'] ?? null,
            'status' => $responseData['status'] ?? null,
            'external_reference' => $responseData['external_reference'] ?? null,
            'qr_code' => null,
            'qr_code_base64' => null,
            'ticket_url' => null
        ];
        
        if (isset($responseData['point_of_interaction']) && 
            isset($responseData['point_of_interaction']['transaction_data'])) {
            
            $transactionData = $responseData['point_of_interaction']['transaction_data'];
            
            $pixData['qr_code'] = $transactionData['qr_code'] ?? null;
            $pixData['qr_code_base64'] = $transactionData['qr_code_base64'] ?? null;
            $pixData['ticket_url'] = $transactionData['ticket_url'] ?? null;
        }
        
        return $pixData;
    }
    
    /**
     * Retorna sugestões para diferentes códigos de erro
     */
    private function getErrorSuggestion($statusCode)
    {
        switch ($statusCode) {
            case 503:
                return 'Serviço temporariamente indisponível. Tente novamente em alguns minutos.';
            case 401:
                return 'Token de acesso inválido. Verifique as credenciais do Mercado Pago.';
            case 403:
                return 'Acesso negado. Verifique as permissões da conta.';
            case 429:
                return 'Muitas requisições. Aguarde antes de tentar novamente.';
            case 500:
                return 'Erro interno do Mercado Pago. Tente novamente mais tarde.';
            default:
                return 'Erro desconhecido. Verifique os logs para mais detalhes.';
        }
    }
}
