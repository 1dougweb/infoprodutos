<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckWebhookStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:check-status {--url=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica o status e conectividade do webhook do Mercado Pago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICANDO STATUS DO WEBHOOK ===');
        
        // URL padrão do webhook
        $webhookUrl = $this->option('url') ?: url('/api/webhooks/mercadopago');
        
        $this->info("URL do webhook: {$webhookUrl}");
        
        // Teste 1: Verificar se a rota responde
        $this->info("\n1. Testando resposta da rota...");
        try {
            $response = Http::timeout(10)->get($webhookUrl);
            $this->info("   Status GET: {$response->status()}");
            
            if ($response->status() === 405) {
                $this->info("   ✅ Rota existe (método GET não permitido, esperado)");
            } else {
                $this->warn("   ⚠️ Status inesperado: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao acessar rota: " . $e->getMessage());
        }
        
        // Teste 2: Verificar se aceita POST
        $this->info("\n2. Testando método POST...");
        try {
            $response = Http::timeout(10)->post($webhookUrl, []);
            $this->info("   Status POST: {$response->status()}");
            
            if ($response->status() === 200) {
                $this->info("   ✅ Webhook aceita POST e retorna 200");
            } else {
                $this->warn("   ⚠️ Status inesperado: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao fazer POST: " . $e->getMessage());
        }
        
        // Teste 3: Verificar headers da resposta
        $this->info("\n3. Verificando headers da resposta...");
        try {
            $response = Http::timeout(10)->post($webhookUrl, []);
            
            $this->info("   Content-Type: " . ($response->header('Content-Type') ?: 'não definido'));
            $this->info("   Content-Length: " . ($response->header('Content-Length') ?: 'não definido'));
            $this->info("   Cache-Control: " . ($response->header('Cache-Control') ?: 'não definido'));
            
            // Verificar se é JSON
            $contentType = $response->header('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $this->info("   ✅ Resposta é JSON válido");
            } else {
                $this->warn("   ⚠️ Resposta não é JSON: {$contentType}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao verificar headers: " . $e->getMessage());
        }
        
        // Teste 4: Verificar se aceita JSON
        $this->info("\n4. Testando envio de JSON...");
        try {
            $testData = [
                'action' => 'test',
                'data' => ['id' => 'test_123']
            ];
            
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($webhookUrl, $testData);
            
            $this->info("   Status com JSON: {$response->status()}");
            
            if ($response->status() === 200) {
                $this->info("   ✅ Webhook aceita JSON e retorna 200");
                
                // Verificar se a resposta é válida
                try {
                    $responseData = $response->json();
                    $this->info("   Resposta: " . json_encode($responseData, JSON_PRETTY_PRINT));
                } catch (\Exception $e) {
                    $this->warn("   ⚠️ Resposta não é JSON válido: " . $response->body());
                }
            } else {
                $this->warn("   ⚠️ Status inesperado com JSON: {$response->status()}");
                $this->info("   Resposta: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao testar JSON: " . $e->getMessage());
        }
        
        // Teste 5: Verificar logs
        $this->info("\n5. Verificando logs...");
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            $logSizeMB = round($logSize / 1024 / 1024, 2);
            $this->info("   Arquivo de log: {$logFile}");
            $this->info("   Tamanho: {$logSizeMB} MB");
            
            // Verificar últimas linhas do log
            $lastLines = $this->getLastLogLines($logFile, 10);
            if (!empty($lastLines)) {
                $this->info("   Últimas linhas do log:");
                foreach ($lastLines as $line) {
                    if (strpos($line, 'webhook') !== false || strpos($line, 'Webhook') !== false) {
                        $this->line("     " . trim($line));
                    }
                }
            }
        } else {
            $this->warn("   ⚠️ Arquivo de log não encontrado");
        }
        
        // Teste 6: Verificar configurações
        $this->info("\n6. Verificando configurações...");
        try {
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if ($accessToken) {
                $this->info("   ✅ Token de acesso configurado: " . substr($accessToken, 0, 10) . "...");
            } else {
                $this->error("   ❌ Token de acesso não configurado");
            }
            
            $webhookUrl = \App\Models\Setting::get('mercadopago_webhook_url');
            if ($webhookUrl) {
                $this->info("   ✅ URL do webhook configurada: {$webhookUrl}");
            } else {
                $this->warn("   ⚠️ URL do webhook não configurada");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao verificar configurações: " . $e->getMessage());
        }
        
        $this->info("\n=== VERIFICAÇÃO CONCLUÍDA ===");
        
        return 0;
    }
    
    /**
     * Obtém as últimas linhas de um arquivo
     */
    private function getLastLogLines($filename, $lines = 10)
    {
        $file = new \SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $start = max(0, $totalLines - $lines);
        $file->seek($start);
        
        $result = [];
        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }
        
        return $result;
    }
}
