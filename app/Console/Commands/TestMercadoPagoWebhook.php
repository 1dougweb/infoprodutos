<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestMercadoPagoWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercadopago:test-webhook {--url=} {--action=payment.created} {--payment-id=123456789}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o webhook do Mercado Pago enviando uma notificação simulada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TESTANDO WEBHOOK DO MERCADO PAGO ===');
        
        // URL padrão do webhook
        $webhookUrl = $this->option('url') ?: url('/api/webhooks/mercadopago');
        $action = $this->option('action');
        $paymentId = $this->option('payment-id');
        
        $this->info("URL do webhook: {$webhookUrl}");
        $this->info("Ação simulada: {$action}");
        $this->info("Payment ID simulado: {$paymentId}");
        
        // Dados simulados do webhook
        $webhookData = [
            'action' => $action,
            'api_version' => 'v1',
            'data' => [
                'id' => $paymentId,
                'status' => 'pending',
                'payment_method_id' => 'pix',
                'payment_type_id' => 'bank_transfer',
                'external_reference' => 'order_123',
                'transaction_amount' => 99.90,
                'installments' => 1,
                'date_created' => now()->format('Y-m-d\TH:i:s.vP'),
                'date_last_updated' => now()->format('Y-m-d\TH:i:s.vP'),
                'payer' => [
                    'id' => 123456789,
                    'first_name' => 'João',
                    'last_name' => 'Silva',
                    'email' => 'joao.silva@exemplo.com',
                    'type' => 'individual',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => '12345678901'
                    ]
                ],
                'point_of_interaction' => [
                    'type' => 'PIX',
                    'sub_type' => 'QR_CODE',
                    'transaction_data' => [
                        'qr_code' => '00020101021226800014br.gov.bcb.pix2558invoice-123@exemplo.com5204000053039865802BR5924João Silva6009São Paulo62070503***6304E2CA',
                        'qr_code_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                        'ticket_url' => 'https://www.mercadopago.com.br/pix/123456789'
                    ]
                ],
                'metadata' => [
                    'preference_id' => 'pref_123456789',
                    'order_id' => 'order_123',
                    'external_reference' => 'order_123'
                ]
            ]
        ];
        
        $this->info('Dados do webhook simulados:');
        $this->line(json_encode($webhookData, JSON_PRETTY_PRINT));
        
        try {
            $this->info('Enviando requisição para o webhook...');
            
            // Fazer a requisição HTTP
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MercadoPago-Webhook-Test/1.0',
                    'X-Test-Webhook' => 'true'
                ])
                ->post($webhookUrl, $webhookData);
            
            $this->info("Status da resposta: {$response->status()}");
            $this->info("Headers da resposta:");
            foreach ($response->headers() as $header => $values) {
                $this->line("  {$header}: " . implode(', ', $values));
            }
            
            $this->info("Body da resposta:");
            $this->line($response->body());
            
            if ($response->successful()) {
                $this->info('✅ Webhook testado com sucesso!');
                Log::info('Webhook testado com sucesso via comando Artisan', [
                    'url' => $webhookUrl,
                    'action' => $action,
                    'payment_id' => $paymentId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            } else {
                $this->error('❌ Webhook retornou erro HTTP: ' . $response->status());
                Log::error('Webhook retornou erro via comando Artisan', [
                    'url' => $webhookUrl,
                    'action' => $action,
                    'payment_id' => $paymentId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao testar webhook: ' . $e->getMessage());
            Log::error('Erro ao testar webhook via comando Artisan', [
                'url' => $webhookUrl,
                'action' => $action,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        
        $this->info('=== TESTE FINALIZADO ===');
        
        return 0;
    }
}
