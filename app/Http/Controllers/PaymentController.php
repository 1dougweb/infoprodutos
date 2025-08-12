<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DigitalProduct;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class PaymentController extends Controller
{
    public function __construct()
    {
        $accessToken = \App\Models\Setting::get('mercadopago_access_token');
        if ($accessToken) {
            \MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
        }
    }

    public function checkout($productId)
    {
        Log::info('=== CHECKOUT INICIADO ===');
        Log::info('Product ID: ' . $productId);
        
        try {
            $product = DigitalProduct::findOrFail($productId);
            Log::info('Produto: ' . $product->title . ' - R$ ' . $product->price);
            
            // Usar usuário logado ou primeiro usuário
            if (!auth()->check()) {
                $user = \App\Models\User::first();
                if ($user) auth()->login($user);
            }
            
            $user = auth()->user();
            Log::info('Usuário: ' . $user->email);

            // Verificar se já possui
            if ($user->hasPurchased($product->id)) {
                return redirect()->route('membership.course', $product->id)
                    ->with('info', 'Você já possui este produto!');
            }

            // Criar pedido
            $order = Order::create([
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'amount' => $product->price,
                'status' => 'pending'
            ]);
            
            Log::info('Pedido criado: ' . $order->id);
            
            // Ir direto para a página de checkout manual
            return $this->manualCheckout($product, $order);
            
        } catch (Exception $e) {
            Log::error('ERRO: ' . $e->getMessage());
            Log::error('Stack: ' . $e->getTraceAsString());
            
            return redirect()->route('membership.index')
                ->with('error', 'Erro: ' . $e->getMessage());
        }
    }
    
    /**
     * Método de checkout manual com links diretos para pagamento
     */
    private function manualCheckout($product, $order)
    {
        Log::info('=== USANDO CHECKOUT MANUAL ===');
        
        // Retornar a página de checkout manual
        return view('payment.checkout', [
            'product' => $product,
            'order' => $order
        ]);
    }
    
    /**
     * Cria uma ordem de pagamento no Mercado Pago usando a API de Orders
     */
    public function createMercadoPagoOrder(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $orderId = $request->input('order_id');
            $paymentMethod = $request->input('payment_method', 'credit_card');
            
            $product = DigitalProduct::findOrFail($productId);
            $order = Order::findOrFail($orderId);
            $user = auth()->user();
            
            // Se não houver usuário autenticado, usar dados do request
            if (!$user) {
                Log::info('Usuário não autenticado, usando dados do request');
                $user = (object) [
                    'email' => $request->input('payer.email', 'cliente@exemplo.com'),
                    'name' => $request->input('payer.first_name', 'Cliente') . ' ' . $request->input('payer.last_name', '')
                ];
            }
            
            // Configurar MP
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                Log::error('Token de acesso do Mercado Pago não configurado');
                return response()->json([
                    'success' => false,
                    'error' => 'Token de acesso do Mercado Pago não configurado'
                ], 500);
            }
            
            \MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
            
            // Para PIX, usar API de Payments diretamente
            if ($paymentMethod === 'pix') {
                Log::info('Gerando PIX via API de Payments');
                return $this->createPixPayment($request, $product, $order, $user, $accessToken);
            }
            
            // Para cartão de crédito, usar API de Payments diretamente
            if ($paymentMethod === 'credit_card') {
                Log::info('Gerando pagamento de cartão via API de Payments');
                return $this->createCreditCardPayment($request, $product, $order, $user, $accessToken);
            }
            
            // Para outros métodos, continuar com API de Orders
            Log::info('Gerando ordem via API de Orders para método: ' . $paymentMethod);
            
            // Gerar um ID de idempotência único
            $idempotencyKey = uniqid('order_') . '_' . $order->id;
            
            // URL do webhook (usando a rota específica sem middleware)
            $webhookUrl = url('/api/webhooks/mercadopago');
            Log::info('URL do webhook configurado: ' . $webhookUrl);
            
            // URLs alternativos para debug
            Log::info('URLs alternativos para webhook:', [
                'webhook_root' => url('/webhook'),
                'webhook_api' => url('/api/webhook'),
                'webhook_payment' => url('/payment/webhook'),
                'webhook_api_mercadopago' => url('/api/webhooks/mercadopago'),
                'webhook_mp' => url('/api/mp-webhook'),
            ]);
            
            // Preparar dados da ordem
            $orderData = [
                "type" => "online",
                "external_reference" => "order_" . $order->id,
                "total_amount" => (string) number_format($product->price, 2, '.', ''),
                "description" => $product->title,
                "payer" => [
                    "email" => $user->email,
                    "entity_type" => "individual",
                    "first_name" => $user->name
                ],
                "shipment" => [
                    "address" => [
                        "zip_code" => "12345678",
                        "street_name" => "Rua Cliente",
                        "street_number" => "123",
                        "neighborhood" => "Centro",
                        "city" => "São Paulo",
                        "state" => "SP"
                    ]
                ]
            ];
            
            // Adicionar transações baseadas no método de pagamento
            if ($paymentMethod === 'credit_card') {
                // Para cartão, usamos os dados fornecidos pelo usuário
                $cardToken = $this->ensureString($request->input('card_token'));
                
                // Se for um token de teste, garantir que tenha pelo menos 32 caracteres
                if (strlen($cardToken) < 32) {
                    $cardToken = str_pad($cardToken, 32, '0', STR_PAD_RIGHT);
                    Log::info('Token ajustado para 32 caracteres: ' . $cardToken);
                }
                
                $orderData["transactions"] = [
                    "payments" => [
                        [
                            "amount" => (string) number_format($product->price, 2, '.', ''),
                            "payment_method" => [
                                "id" => $this->ensureString($request->input('card_brand', 'visa')),
                                "type" => "credit_card",
                                "token" => $cardToken,
                                "installments" => (int) $request->input('installments', 1),
                                "statement_descriptor" => "Compra Online"
                            ]
                        ]
                    ]
                ];
                
                // Adicionar dados do pagador
                if ($request->filled('card_holder_name') && $request->filled('card_holder_doc')) {
                    $orderData["payer"] = array_merge($orderData["payer"], [
                        "first_name" => $this->ensureString($request->input('card_holder_name')),
                        "identification" => [
                            "type" => "CPF",
                            "number" => preg_replace('/\D/', '', $this->ensureString($request->input('card_holder_doc')))
                        ]
                    ]);
                }
            }
            
            Log::info('Dados da ordem para Mercado Pago: ' . json_encode($orderData));
            
            // Fazer a requisição para a API do Mercado Pago
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.mercadopago.com/v1/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey
                ],
                'json' => $orderData
            ]);
            
            // Processar resposta
            $responseData = json_decode($response->getBody(), true);
            Log::info('Resposta da API do Mercado Pago: ' . json_encode($responseData));
            
            // Atualizar o pedido com os dados do Mercado Pago
            $order->update([
                'mercadopago_order_id' => $responseData['id'],
                'status' => $responseData['status']
            ]);
            
            // Verificar se o status é realmente aprovado
            $orderStatus = $responseData['status'];
            $isSuccess = $orderStatus === 'open' || $orderStatus === 'paid'; // Orders podem ter status 'open' ou 'paid'
            
            // Log do status da ordem
            Log::info('Status da ordem retornado: ' . $orderStatus);
            Log::info('Ordem criada com sucesso: ' . ($isSuccess ? 'Sim' : 'Não'));
            
            // Se a ordem foi criada com sucesso, liberar o produto imediatamente
            if ($isSuccess) {
                Log::info('Ordem criada com sucesso - liberando produto imediatamente para o usuário: ' . $user->email);
                
                // Atualizar status do pedido para aprovado
                $order->update([
                    'status' => 'approved',
                    'paid_at' => now(),
                    'payment_method' => 'credit_card'
                ]);
                
                // Criar compra do usuário (liberar produto)
                $this->createUserPurchase($user, $order->digitalProduct);
                
                Log::info('Produto liberado com sucesso para o usuário: ' . $user->email);
            }
            
            // Retornar dados para o frontend
            return response()->json([
                'success' => $isSuccess,
                'order_id' => $responseData['id'],
                'status' => $orderStatus,
                'payment_details' => $responseData['transactions'] ?? null,
                'client_token' => $responseData['client_token'] ?? null,
                'message' => $isSuccess ? 
                    'Ordem criada com sucesso e produto liberado!' : 
                    'Ordem não criada com sucesso: ' . $orderStatus
            ]);
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = (string) $e->getResponse()->getBody();
            $statusCode = $e->getResponse()->getStatusCode();
            
            Log::error('Erro HTTP na API do Mercado Pago: ' . $statusCode);
            Log::error('Body da resposta: ' . $body);
            Log::error('Headers da resposta: ' . json_encode($e->getResponse()->getHeaders()));
            
            return response()->json([
                'success' => false,
                'error' => 'Erro na API do Mercado Pago: ' . $statusCode,
                'details' => json_decode($body, true),
                'status_code' => $statusCode
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar ordem no Mercado Pago: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }
    
    /**
     * Cria um pagamento PIX diretamente via API de Payments
     */
    private function createPixPayment(Request $request, $product, $order, $user, $accessToken)
    {
        try {
            Log::info('=== CRIANDO PAGAMENTO PIX ===');
            Log::info('Produto: ' . $product->title . ' - R$ ' . $product->price);
            Log::info('Pedido: ' . $order->id);
            Log::info('Usuário: ' . $user->email);
            
            // Configurar URL do webhook
            $webhookUrl = url('/api/webhooks/mercadopago');
            Log::info('URL do webhook: ' . $webhookUrl);
            
            // Preparar payload para API de Payments
            $payload = [
                'description' => 'Pagamento PIX para ' . $product->title,
                'external_reference' => 'order_' . $order->id,
                'payer' => [
                    'email' => $this->ensureString($request->input('payer.email')),
                    'first_name' => $this->ensureString($request->input('payer.first_name', 'Cliente')),
                    'last_name' => $this->ensureString($request->input('payer.last_name', '')),
                    'entity_type' => 'individual'
                ],
                'payment_method_id' => 'pix',
                'transaction_amount' => (float) $product->price
            ];
            
            // Adicionar dados de identificação se disponível
            if ($request->filled('payer.identification.type') && $request->filled('payer.identification.number')) {
                $payload['payer']['identification'] = [
                    'type' => $this->ensureString($request->input('payer.identification.type')),
                    'number' => preg_replace('/\D/', '', $this->ensureString($request->input('payer.identification.number')))
                ];
            }
            
            // Adicionar dados adicionais do pagador se fornecidos
            if ($request->filled('payer.first_name')) {
                $payload['payer']['first_name'] = $this->ensureString($request->input('payer.first_name'));
            }
            if ($request->filled('payer.last_name')) {
                $payload['payer']['last_name'] = $this->ensureString($request->input('payer.last_name'));
            }
            
            Log::info('Payload para API de Payments PIX:', $payload);
            
            // Gerar ID de idempotência único
            $idempotencyKey = md5('pix_' . $order->id . '_' . time() . '_' . rand(1000, 9999));
            
            // Fazer requisição para API de Payments
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.mercadopago.com/v1/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey
                ],
                'json' => $payload,
                'timeout' => 30
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            Log::info('Resposta da API de Payments PIX:', $responseData);
            
            if (isset($responseData['id'])) {
                // Atualizar o pedido com o ID do pagamento
                $order->update([
                    'mercadopago_payment_id' => $responseData['id'],
                    'mercadopago_order_id' => $responseData['id'], // Para compatibilidade
                    'status' => $responseData['status']
                ]);
                
                // Verificar se o pagamento foi realmente aprovado
                $paymentStatus = $responseData['status'];
                $statusDetail = $responseData['status_detail'] ?? '';
                
                // Considerar como sucesso: approved, in_process, ou pending (exceto rejeitados)
                $isSuccess = in_array($paymentStatus, ['approved', 'in_process', 'pending']) && 
                             !in_array($statusDetail, ['cc_rejected_high_risk', 'cc_rejected_bad_filled_security_code', 'cc_rejected_bad_filled_date', 'cc_rejected_bad_filled_other']);
                
                // Log do status do pagamento
                Log::info('Status do pagamento PIX retornado: ' . $paymentStatus);
                Log::info('Status detail: ' . $statusDetail);
                Log::info('Pagamento PIX aceito: ' . ($isSuccess ? 'Sim' : 'Não'));
                
                // Se o pagamento foi aceito, liberar o produto imediatamente
                if ($isSuccess) {
                    Log::info('Pagamento PIX aceito - liberando produto imediatamente para o usuário: ' . $user->email);
                    
                    // Atualizar status do pedido para aprovado
                    $order->update([
                        'status' => 'approved',
                        'paid_at' => now(),
                        'payment_method' => 'pix'
                    ]);
                    
                    // Criar compra do usuário (liberar produto)
                    $this->createUserPurchase($user, $order->digitalProduct);
                    
                    Log::info('Produto liberado com sucesso para o usuário: ' . $user->email);
                }
                
                // Preparar mensagem de erro mais clara
                $errorMessage = $this->getErrorMessage($paymentStatus, $statusDetail);
                
                // Extrair dados do PIX
                $pixData = $this->extractPixData($responseData);
                
                Log::info('Pagamento PIX criado:', [
                    'payment_id' => $responseData['id'],
                    'status' => $paymentStatus,
                    'status_detail' => $statusDetail,
                    'pix_data' => $pixData
                ]);
                
                return response()->json([
                    'success' => $isSuccess,
                    'payment_id' => $responseData['id'],
                    'status' => $paymentStatus,
                    'status_detail' => $statusDetail,
                    'pix_data' => $pixData,
                    'message' => $isSuccess ? 
                        'Pagamento PIX aceito e produto liberado!' : 
                        $errorMessage
                ]);
                
            } else {
                Log::error('Resposta da API sem ID de pagamento:', $responseData);
                return response()->json([
                    'success' => false,
                    'error' => 'Resposta da API sem ID de pagamento',
                    'details' => $responseData
                ], 400);
            }
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = (string) $e->getResponse()->getBody();
            $statusCode = $e->getResponse()->getStatusCode();
            
            Log::error('Erro HTTP na API de Payments PIX: ' . $statusCode);
            Log::error('Body da resposta: ' . $body);
            Log::error('Headers da resposta: ' . json_encode($e->getResponse()->getHeaders()));
            
            // Log específico para erro 503
            if ($statusCode === 503) {
                Log::error('ERRO 503 - Serviço indisponível. Possíveis causas:');
                Log::error('- API do Mercado Pago temporariamente indisponível');
                Log::error('- Rate limiting atingido');
                Log::error('- Problemas de conectividade');
                Log::error('- Token de acesso inválido ou expirado');
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Erro na API do Mercado Pago: ' . $statusCode,
                'details' => json_decode($body, true),
                'status_code' => $statusCode,
                'suggestion' => $this->getErrorSuggestion($statusCode)
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento PIX: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }
    
    /**
     * Extrai dados do PIX da resposta da API
     */
    private function extractPixData($responseData)
    {
        $pixData = [
            'payment_id' => $responseData['id'] ?? null,
            'status' => $responseData['status'] ?? null,
            'external_reference' => $responseData['external_reference'] ?? null,
            'qr_code' => null,
            'qr_code_base64' => null,
            'ticket_url' => null
        ];
        
        // Extrair dados do PIX se disponível
        if (isset($responseData['point_of_interaction']) && 
            isset($responseData['point_of_interaction']['transaction_data'])) {
            
            $transactionData = $responseData['point_of_interaction']['transaction_data'];
            
            $pixData['qr_code'] = $transactionData['qr_code'] ?? null;
            $pixData['qr_code_base64'] = $transactionData['qr_code_base64'] ?? null;
            $pixData['ticket_url'] = $transactionData['ticket_url'] ?? null;
            
            // Log dos dados PIX extraídos
            if ($pixData['qr_code']) {
                Log::info('=== DADOS PIX EXTRAÍDOS ===');
                Log::info('QR Code: ' . $pixData['qr_code']);
                Log::info('QR Code Base64: ' . ($pixData['qr_code_base64'] ? 'disponível' : 'não disponível'));
                Log::info('Ticket URL: ' . ($pixData['ticket_url'] ?? 'não disponível'));
                Log::info('=== FIM DADOS PIX ===');
            }
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
    
    /**
     * Gera QR code PIX para um pedido específico
     */
    public function generatePixQRCode(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $productId = $request->input('product_id');
            
            Log::info('=== GERANDO QR CODE PIX ===');
            Log::info('Produto ID: ' . $productId . ' e Pedido ID: ' . $orderId);
            
            $order = Order::findOrFail($orderId);
            $product = DigitalProduct::findOrFail($productId);
            
            Log::info('Produto: ' . $product->title . ' - R$ ' . $product->price);
            Log::info('Pedido: ' . $order->id . ' - Status: ' . $order->status);
            
            // Validar apenas campos essenciais
            $request->validate([
                'payer.email' => 'required|email'
            ]);
            
            // Configurar o token de acesso do Mercado Pago
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                Log::error('Token de acesso do Mercado Pago não configurado');
                return response()->json(['error' => 'Token de acesso do Mercado Pago não configurado'], 500);
            }
            
            Log::info('Token de acesso configurado com sucesso');
            
            // Usar a URL correta do webhook
            $webhookUrl = url('/api/webhooks/mercadopago');
            Log::info('URL do webhook configurada: ' . $webhookUrl);
            
            // Verificar se estamos em ambiente local
            $isLocal = in_array(request()->getHost(), ['127.0.0.1', 'localhost', '127.0.0.1:8000', 'localhost:8000']);
            
            if ($isLocal) {
                Log::info('Ambiente local detectado - removendo notification_url para evitar erro do Mercado Pago');
                $webhookUrl = null;
            }

            $payload = [
                'description' => 'Pagamento PIX para ' . $product->title,
                'external_reference' => 'order_' . $order->id,
                'payer' => [
                    'email' => $this->ensureString($request->input('payer.email')),
                    'first_name' => $this->ensureString($request->input('payer.first_name', 'Cliente')),
                    'last_name' => $this->ensureString($request->input('payer.last_name', '')),
                    'entity_type' => 'individual'
                ],
                'payment_method_id' => 'pix',
                'transaction_amount' => (float) $product->price
            ];
            
            // Só incluir notification_url se for uma URL pública válida
            if ($webhookUrl && !$isLocal) {
                $payload['notification_url'] = $webhookUrl;
                Log::info('Notification URL incluída: ' . $webhookUrl);
            } else {
                Log::info('Notification URL não incluída (ambiente local ou URL inválida)');
            }
            
            // Adicionar campos opcionais do pagador se fornecidos
            if ($request->filled('payer.identification.type') && $request->filled('payer.identification.number')) {
                $payload['payer']['identification'] = [
                    'type' => $this->ensureString($request->input('payer.identification.type')),
                    'number' => preg_replace('/\D/', '', $this->ensureString($request->input('payer.identification.number')))
                ];
            }
            
            Log::info('Payload para API do Mercado Pago:', $payload);
            
            // Fazer a requisição para a API de pagamentos
            $client = new \GuzzleHttp\Client();
            
            // Gerar um ID de idempotência único
            $idempotencyKey = md5('pix_' . $order->id . '_' . time() . '_' . rand(1000, 9999));
            
            Log::info('Fazendo requisição para API de Payments com ID de idempotência: ' . $idempotencyKey);
            
            try {
                $response = $client->post('https://api.mercadopago.com/v1/payments', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'X-Idempotency-Key' => $idempotencyKey
                    ],
                    'json' => $payload,
                    'timeout' => 30
                ]);
                
                $responseData = json_decode($response->getBody(), true);
                Log::info('Resposta da API do Mercado Pago:', $responseData);
                
                if (isset($responseData['id'])) {
                    // Atualizar o pedido com o ID do pagamento
                    $order->update([
                        'mercadopago_payment_id' => $responseData['id'],
                        'mercadopago_order_id' => $responseData['id'], // Para compatibilidade
                        'status' => $responseData['status']
                    ]);
                    
                    Log::info('Pedido atualizado com payment_id: ' . $responseData['id']);
                    
                    // Extrair dados do QR code para PIX
                    $pixData = $this->extractPixData($responseData);
                    
                    Log::info('Dados PIX extraídos:', $pixData);
                    
                    return response()->json([
                        'success' => true,
                        'pix_data' => $pixData,
                        'message' => 'QR Code PIX gerado com sucesso'
                    ]);
                } else {
                    Log::error('Resposta da API sem ID de pagamento:', $responseData);
                    return response()->json(['error' => 'Resposta da API sem ID de pagamento', 'details' => $responseData], 400);
                }
                
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();
                
                Log::error('Erro HTTP na API de Payments PIX: ' . $statusCode);
                Log::error('Body da resposta: ' . $body);
                Log::error('Headers da resposta: ' . json_encode($e->getResponse()->getHeaders()));
                
                // Log específico para erro 503
                if ($statusCode === 503) {
                    Log::error('ERRO 503 - Serviço indisponível. Possíveis causas:');
                    Log::error('- API do Mercado Pago temporariamente indisponível');
                    Log::error('- Rate limiting atingido');
                    Log::error('- Problemas de conectividade');
                    Log::error('- Token de acesso inválido ou expirado');
                    Log::error('- Payload inválido');
                }
                
                return response()->json([
                    'error' => 'Erro na API do Mercado Pago: ' . $statusCode,
                    'details' => json_decode($body, true),
                    'status_code' => $statusCode,
                    'suggestion' => $this->getErrorSuggestion($statusCode)
                ], 400);
                
            } catch (\Exception $e) {
                Log::error('Erro ao gerar QR Code PIX: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                Log::error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
                
                return response()->json(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR Code PIX: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Gera um código PIX a partir de uma preferência do Mercado Pago
     */
    private function generatePixCodeFromPreference($preference)
    {
        try {
            Log::info('Gerando código PIX a partir da preferência');
            
            // Verificar se a preferência tem ID
            if (!isset($preference->id)) {
                Log::error('Preferência sem ID');
                throw new \Exception('Preferência sem ID');
            }
            
            // Verificar se a preferência tem init_point
            if (!isset($preference->init_point)) {
                Log::error('Preferência sem init_point');
                throw new \Exception('Preferência sem init_point');
            }
            
            // Tentar obter o código PIX real do Mercado Pago
            try {
                $accessToken = \App\Models\Setting::get('mercadopago_access_token');
                MercadoPagoConfig::setAccessToken($accessToken);
                
                // Fazer uma requisição para obter detalhes da preferência
                $client = new \GuzzleHttp\Client();
                $response = $client->get('https://api.mercadopago.com/v1/payment_methods/pix/payment_places', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'query' => [
                        'preference_id' => $preference->id
                    ]
                ]);
                
                $responseData = json_decode($response->getBody(), true);
                Log::info('Resposta da API do Mercado Pago para código PIX: ' . json_encode($responseData));
                
                // Verificar se temos um código PIX válido na resposta
                if (isset($responseData['qr_code'])) {
                    Log::info('Código PIX obtido da API do Mercado Pago');
                    return $responseData['qr_code'];
                }
                
                // Se não conseguiu obter o código PIX, usar o init_point para gerar um QR code
                Log::info('Usando init_point como código PIX: ' . $preference->init_point);
                return $preference->init_point;
                
            } catch (\Exception $apiEx) {
                Log::error('Erro ao obter código PIX da API: ' . $apiEx->getMessage());
                
                // Se falhar a obtenção via API, usar o init_point como código
                Log::info('Usando init_point como código PIX após falha: ' . $preference->init_point);
                return $preference->init_point;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar código PIX: ' . $e->getMessage());
            
            // Fallback: gerar um código PIX fictício com timestamp
            $fallbackCode = 'https://www.mercadopago.com.br/pix/' . substr(md5(time()), 0, 16);
            Log::info('Usando código PIX fallback: ' . $fallbackCode);
            return $fallbackCode;
        }
    }
    
    /**
     * Gera dados para pagamento via PIX quando a API do Mercado Pago falha
     */
    private function generatePixData($product, $order)
    {
        // Gerar QR code usando Google Charts API
        $pixCode = '00020101021226800014br.gov.bcb.pix2558invoice-' . $order->id . '@seu-dominio.com.br5204000053039865802BR5924NOME DO BENEFICIARIO6009SAO PAULO62070503***6304E2CA';
        $qrCodeUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($pixCode);
        
        // Fazer requisição para obter a imagem do QR code em base64
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($qrCodeUrl);
            $imageData = $response->getBody();
            $base64Image = base64_encode($imageData);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR code alternativo: ' . $e->getMessage());
            $base64Image = null;
        }
        
        return [
            'qr_code' => $pixCode,
            'qr_code_base64' => $base64Image,
            'ticket_url' => $qrCodeUrl,
            'date_of_expiration' => now()->addMinutes(30)->format('Y-m-d\TH:i:s.vP')
        ];
    }
    
    /**
     * Método alternativo de checkout quando a API do Mercado Pago falha
     * @deprecated Use manualCheckout() instead
     */
    private function checkoutAlternative($product, $order)
    {
        return $this->manualCheckout($product, $order);
    }

    public function success(Request $request)
    {
        Log::info('Payment success chamado', $request->all());

        $paymentId = $request->query('payment_id');
        $preferenceId = $request->query('preference_id');

        // Buscar pedido por payment_id ou preference_id
        $order = null;
        if ($paymentId) {
            $order = Order::where('mercadopago_payment_id', $paymentId)->first();
        }
        if (!$order && $preferenceId) {
            $order = Order::where('mercadopago_preference_id', $preferenceId)->first();
        }

        if (!$order) {
            Log::error('Pedido não encontrado para payment_id: ' . $paymentId . ' ou preference_id: ' . $preferenceId);
            return redirect()->route('membership.index')->with('error', 'Pedido não encontrado.');
        }

        // Se o pedido já foi aprovado, apenas redirecionar
        if ($order->status === 'approved') {
            Log::info('Pedido já aprovado. Redirecionando para área de membros.');
            return redirect()->route('membership.index')->with('success', 'Pagamento aprovado! Produto disponível.');
        }

        // Determinar método de pagamento baseado no payment_id
        $paymentMethod = 'unknown';
        $paymentStatus = 'unknown';
        
        if ($paymentId) {
            try {
                $client = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $client->get($paymentId);
                
                // Obter status real do pagamento
                $paymentStatus = $payment->status ?? 'unknown';
                Log::info('Status real do pagamento no Mercado Pago: ' . $paymentStatus);
                
                // Verificar se o pagamento foi realmente aprovado
                if ($paymentStatus !== 'approved') {
                    Log::warning('Pagamento não aprovado. Status: ' . $paymentStatus);
                    return redirect()->route('membership.index')->with('error', 
                        'Pagamento não aprovado. Status: ' . $paymentStatus . 
                        ($payment->status_detail ? ' - ' . $payment->status_detail : ''));
                }
                
                // Determinar método baseado no payment_method_id do Mercado Pago
                $mpPaymentMethodId = $payment->payment_method_id ?? '';
                $mpPaymentTypeId = $payment->payment_type_id ?? '';
                
                Log::info('Payment Method ID: ' . $mpPaymentMethodId);
                Log::info('Payment Type ID: ' . $mpPaymentTypeId);
                
                // Mapear métodos do Mercado Pago para nossos métodos
                if (strpos($mpPaymentMethodId, 'pix') !== false || strpos($mpPaymentTypeId, 'pix') !== false) {
                    $paymentMethod = 'pix';
                } elseif (strpos($mpPaymentMethodId, 'bolbradesco') !== false || strpos($mpPaymentTypeId, 'ticket') !== false) {
                    $paymentMethod = 'boleto';
                } elseif (in_array($mpPaymentTypeId, ['credit_card', 'debit_card']) || 
                         in_array($mpPaymentMethodId, ['visa', 'master', 'elo', 'amex', 'hipercard'])) {
                    $paymentMethod = 'credit_card';
                } elseif (strpos($mpPaymentMethodId, 'transfer') !== false || strpos($mpPaymentTypeId, 'transfer') !== false) {
                    $paymentMethod = 'transfer';
                } else {
                    // Se não conseguir determinar, usar o payment_method_id original
                    $paymentMethod = $mpPaymentMethodId ?: 'unknown';
                }
                
                Log::info('Método de pagamento determinado: ' . $paymentMethod);
            } catch (Exception $e) {
                Log::error('Erro ao obter informações do pagamento: ' . $e->getMessage());
                return redirect()->route('membership.index')->with('error', 
                    'Erro ao verificar status do pagamento. Por favor, entre em contato com o suporte.');
            }
        } else {
            Log::error('Payment ID não fornecido');
            return redirect()->route('membership.index')->with('error', 'ID do pagamento não fornecido.');
        }

        // Só aprovar se o status for realmente 'approved'
        if ($paymentStatus === 'approved') {
            // Atualizar status do pedido
            $order->update([
                'mercadopago_payment_id' => $paymentId,
                'status' => 'approved',
                'paid_at' => now(),
                'payment_method' => $paymentMethod
            ]);

            // Criar compra do usuário
            $this->createUserPurchase($order->user, $order->digitalProduct);

            Log::info('Pagamento aprovado com sucesso. Produto liberado para o usuário: ' . $order->user->email);

            return redirect()->route('membership.index')->with('success', 'Pagamento aprovado! Produto disponível para acesso.');
        } else {
            Log::warning('Pagamento não aprovado. Status: ' . $paymentStatus);
            return redirect()->route('membership.index')->with('error', 
                'Pagamento não aprovado. Status: ' . $paymentStatus);
        }
    }

    public function failure(Request $request)
    {
        Log::info('Payment failure chamado', $request->all());
        
        $paymentId = $request->query('payment_id');
        $preferenceId = $request->query('preference_id');
        
        // Buscar pedido para atualizar status
        $order = null;
        if ($paymentId) {
            $order = Order::where('mercadopago_payment_id', $paymentId)->first();
        }
        if (!$order && $preferenceId) {
            $order = Order::where('mercadopago_preference_id', $preferenceId)->first();
        }
        
        if ($order) {
            $order->update(['status' => 'failed']);
            Log::info('Pedido marcado como falha: ' . $order->id);
        }
        
        return redirect()->route('membership.index')->with('error', 'Pagamento não foi aprovado. Tente novamente ou entre em contato conosco.');
    }

    public function pending(Request $request)
    {
        Log::info('Payment pending chamado', $request->all());
        
        $paymentId = $request->query('payment_id');
        $preferenceId = $request->query('preference_id');
        
        // Buscar pedido para atualizar status
        $order = null;
        if ($paymentId) {
            $order = Order::where('mercadopago_payment_id', $paymentId)->first();
        }
        if (!$order && $preferenceId) {
            $order = Order::where('mercadopago_preference_id', $preferenceId)->first();
        }
        
        if ($order) {
            $order->update(['status' => 'pending']);
            Log::info('Pedido marcado como pendente: ' . $order->id);
        }
        
        return redirect()->route('membership.index')->with('warning', 'Pagamento em análise. Você será notificado quando for aprovado.');
    }

    /**
     * Webhook para receber notificações do Mercado Pago
     * 
     * IMPORTANTE: Este método deve SEMPRE retornar 200 OK ou 201 CREATED para o Mercado Pago,
     * mesmo em caso de erro, para evitar que o Mercado Pago continue reenviando a notificação.
     * O Mercado Pago aguarda 22 segundos por uma resposta e fará novas tentativas a cada 15 minutos.
     * 
     * @see https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
     */
    public function webhook(Request $request)
    {
        // Log inicial com timestamp
        $timestamp = now()->format('Y-m-d H:i:s.u');
        Log::info("=== WEBHOOK RECEBIDO [{$timestamp}] ===");
        Log::info('Webhook URL: ' . $request->fullUrl());
        Log::info('Webhook método: ' . $request->method());
        Log::info('Webhook IP: ' . $request->ip());
        Log::info('Webhook User-Agent: ' . $request->header('User-Agent'));
        Log::info('Webhook Content-Type: ' . $request->header('Content-Type'));
        Log::info('Webhook Content-Length: ' . $request->header('Content-Length'));
        
        // Log dos headers importantes
        $importantHeaders = [
            'Authorization', 'X-Idempotency-Key', 'X-Request-ID', 
            'X-Forwarded-For', 'X-Real-IP', 'CF-Connecting-IP'
        ];
        
        foreach ($importantHeaders as $header) {
            if ($request->header($header)) {
                Log::info("Webhook Header {$header}: " . $request->header($header));
            }
        }
        
        // Log do body da requisição
        $body = $request->all();
        Log::info('Webhook Body (raw): ' . json_encode($body, JSON_PRETTY_PRINT));
        
        // Log do body como string para debug
        $rawBody = $request->getContent();
        Log::info('Webhook Raw Body: ' . $rawBody);
        
        try {
            // Configurar o token de acesso do Mercado Pago
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            if (empty($accessToken)) {
                Log::error('Webhook - Token de acesso do Mercado Pago não configurado');
                Log::info('Webhook - Retornando 200 OK com erro de configuração');
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Mercado Pago access token not configured',
                    'timestamp' => $timestamp,
                    'webhook_id' => uniqid('webhook_')
                ], 200);
            }
            
            Log::info('Webhook - Token de acesso configurado com sucesso');
            MercadoPagoConfig::setAccessToken($accessToken);
            
            // Processar diferentes tipos de notificações
            if (isset($body['action']) && $body['action'] === 'payment.created' && isset($body['data']['id'])) {
                // Notificação de pagamento criado
                $paymentId = $body['data']['id'];
                Log::info('Webhook - Processando notificação de pagamento criado - ID: ' . $paymentId);
                Log::info('Webhook - Dados do pagamento: ' . json_encode($body['data']));
                
                // Log adicional para debug do payment.created
                if (isset($body['data']['status'])) {
                    Log::info('Webhook - Status do pagamento: ' . $body['data']['status']);
                }
                if (isset($body['data']['payment_method_id'])) {
                    Log::info('Webhook - Método de pagamento: ' . $body['data']['payment_method_id']);
                }
                if (isset($body['data']['external_reference'])) {
                    Log::info('Webhook - Referência externa: ' . $body['data']['external_reference']);
                }
                
                $this->processPayment($paymentId);
            } 
            else if (isset($body['action']) && $body['action'] === 'payment.updated' && isset($body['data']['id'])) {
                // Notificação de pagamento atualizado
                $paymentId = $body['data']['id'];
                Log::info('Webhook - Processando notificação de pagamento atualizado - ID: ' . $paymentId);
                Log::info('Webhook - Dados do pagamento: ' . json_encode($body['data']));
                
                $this->processPayment($paymentId);
            }
            else if (isset($body['action']) && ($body['action'] === 'order.created' || $body['action'] === 'order.updated') && isset($body['data']['id'])) {
                // Notificação de ordem criada ou atualizada
                $orderId = $body['data']['id'];
                Log::info('Webhook - Processando notificação de ordem ' . $body['action'] . ' - ID: ' . $orderId);
                Log::info('Webhook - Dados da ordem: ' . json_encode($body['data']));
                $this->processOrder($orderId);
            }
            else {
                Log::info('Webhook - Tipo de notificação não identificada ou dados incompletos');
                Log::info('Webhook - Action recebida: ' . ($body['action'] ?? 'não informado'));
                Log::info('Webhook - Dados completos: ' . json_encode($body, JSON_PRETTY_PRINT));
                
                // Log específico para debug do erro 503
                if (isset($body['error']) || isset($body['status']) && $body['status'] === 'error') {
                    Log::warning('Webhook - Possível erro detectado nos dados: ' . json_encode($body));
                }
            }
            
            Log::info('Webhook - Processamento concluído com sucesso');
            
            // Sempre retornar 200 OK para o Mercado Pago
            return response()->json([
                'status' => 'ok',
                'message' => 'Webhook processado com sucesso',
                'timestamp' => $timestamp,
                'webhook_id' => uniqid('webhook_'),
                'processed_action' => $body['action'] ?? 'unknown',
                'data_id' => $body['data']['id'] ?? null
            ], 200);
            
        } catch (Exception $e) {
            Log::error('Webhook - Erro durante o processamento: ' . $e->getMessage());
            Log::error('Webhook - Stack trace: ' . $e->getTraceAsString());
            Log::error('Webhook - Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            
            // Log adicional para debug
            Log::error('Webhook - Tipo de exceção: ' . get_class($e));
            Log::error('Webhook - Código de erro: ' . $e->getCode());
            
            // Mesmo com erro, retornar 200 para o Mercado Pago não reenviar
            Log::info('Webhook - Retornando 200 OK mesmo com erro para evitar reenvio');
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage(),
                'error_type' => get_class($e),
                'error_code' => $e->getCode(),
                'timestamp' => $timestamp,
                'webhook_id' => uniqid('webhook_error_')
            ], 200);
        } finally {
            Log::info("=== WEBHOOK FINALIZADO [{$timestamp}] ===");
        }
    }
    
    /**
     * Processa uma notificação de pagamento do Mercado Pago
     */
    private function processPayment($paymentId)
    {
        try {
            Log::info('=== PROCESSANDO PAGAMENTO ===');
            Log::info('Payment ID: ' . $paymentId);
            
            $client = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($paymentId);
            
            // Log da resposta completa para debug
            Log::info('Resposta completa do pagamento: ' . json_encode($payment, JSON_PRETTY_PRINT));
            
            // Log detalhado dos campos importantes
            Log::info('Detalhes do pagamento:', [
                'id' => $payment->id ?? 'não informado',
                'status' => $payment->status ?? 'não informado',
                'status_detail' => $payment->status_detail ?? 'não informado',
                'payment_method_id' => $payment->payment_method_id ?? 'não informado',
                'payment_type_id' => $payment->payment_type_id ?? 'não informado',
                'external_reference' => $payment->external_reference ?? 'não informado',
                'transaction_amount' => $payment->transaction_amount ?? 'não informado',
                'installments' => $payment->installments ?? 'não informado',
                'date_created' => $payment->date_created ?? 'não informado',
                'date_approved' => $payment->date_approved ?? 'não informado',
                'date_last_updated' => $payment->date_last_updated ?? 'não informado'
            ]);
            
            // Log específico para PIX
            if (isset($payment->point_of_interaction)) {
                Log::info('Dados do Ponto de Interação (PIX):', [
                    'type' => $payment->point_of_interaction->type ?? 'não informado',
                    'sub_type' => $payment->point_of_interaction->sub_type ?? 'não informado'
                ]);
                
                if (isset($payment->point_of_interaction->transaction_data)) {
                    $transactionData = $payment->point_of_interaction->transaction_data;
                    Log::info('Dados da Transação PIX:', [
                        'qr_code' => $transactionData->qr_code ?? 'não informado',
                        'qr_code_base64' => $transactionData->qr_code_base64 ?? 'não informado',
                        'ticket_url' => $transactionData->ticket_url ?? 'não informado'
                    ]);
                    
                    // Log do código PIX gerado
                    if (isset($transactionData->qr_code)) {
                        Log::info('=== CÓDIGO PIX GERADO ===');
                        Log::info('QR Code: ' . $transactionData->qr_code);
                        Log::info('QR Code Base64: ' . ($transactionData->qr_code_base64 ? 'disponível' : 'não disponível'));
                        Log::info('Ticket URL: ' . ($transactionData->ticket_url ?? 'não disponível'));
                        Log::info('=== FIM CÓDIGO PIX ===');
                    }
                }
            }
            
            // Log específico para cartão de crédito
            if (isset($payment->card)) {
                Log::info('Dados do Cartão:', [
                    'last_four_digits' => $payment->card->last_four_digits ?? 'não informado',
                    'cardholder_name' => $payment->card->cardholder_name ?? 'não informado',
                    'expiration_month' => $payment->card->expiration_month ?? 'não informado',
                    'expiration_year' => $payment->card->expiration_year ?? 'não informado'
                ]);
            }
            
            // Registrar dados do comprador
            if (isset($payment->payer)) {
                $payerData = [
                    'id' => $payment->payer->id ?? 'não informado',
                    'first_name' => $payment->payer->first_name ?? 'não informado',
                    'last_name' => $payment->payer->last_name ?? 'não informado',
                    'email' => $payment->payer->email ?? 'não informado',
                    'type' => $payment->payer->type ?? 'não informado'
                ];
                
                // Verificar se há dados de identificação
                if (isset($payment->payer->identification)) {
                    $payerData['identification_type'] = $payment->payer->identification->type ?? 'não informado';
                    $payerData['identification_number'] = $payment->payer->identification->number ?? 'não informado';
                } else {
                    $payerData['identification_type'] = 'não disponível';
                    $payerData['identification_number'] = 'não disponível';
                }
                
                Log::info('Dados do comprador:', $payerData);
            }
            
            // Log de metadados se disponível
            if (isset($payment->metadata)) {
                Log::info('Metadados do pagamento:', [
                    'preference_id' => $payment->metadata->preference_id ?? 'não informado',
                    'order_id' => $payment->metadata->order_id ?? 'não informado',
                    'external_reference' => $payment->metadata->external_reference ?? 'não informado'
                ]);
            }
            
            // Encontrar o pedido correspondente
            $order = $this->findOrder([
                'external_reference' => $payment->external_reference ?? null,
                'payment_id' => $paymentId,
                'preference_id' => $payment->metadata->preference_id ?? null,
                'order_id' => $payment->metadata->order_id ?? null
            ]);
            
            if (!$order) {
                Log::warning('Pedido não encontrado para o pagamento: ' . $paymentId);
                Log::warning('Tentativas de busca:', [
                    'external_reference' => $payment->external_reference ?? 'não informado',
                    'payment_id' => $paymentId,
                    'preference_id' => $payment->metadata->preference_id ?? 'não informado',
                    'order_id' => $payment->metadata->order_id ?? 'não informado'
                ]);
                return;
            }
            
            Log::info('Pedido encontrado:', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'product_id' => $order->digital_product_id,
                'status_atual' => $order->status
            ]);
            
            // Processar o pagamento com base no status
            if ($payment->status == 'approved') {
                // VERIFICAÇÃO CRÍTICA: Validar se é um cartão de teste válido
                if ($this->isValidTestCard($payment)) {
                    Log::info('Pagamento aprovado - processando aprovação do pedido');
                    $this->approveOrder($order, $payment);
                } else {
                    Log::warning('Cartão de teste rejeitado - não processando aprovação');
                    // Atualizar status para rejeitado
                    $order->update([
                        'status' => 'rejected',
                        'mercadopago_payment_id' => $paymentId
                    ]);
                }
            } else {
                Log::info('Pagamento não aprovado. Status: ' . ($payment->status ?? 'unknown'));
                Log::info('Status detail: ' . ($payment->status_detail ?? 'não informado'));
                
                // Mapear status do Mercado Pago para status local
                $localStatus = $this->mapMercadoPagoStatus($payment->status, $payment->status_detail);
                
                // Atualizar o status do pedido
                $order->update([
                    'status' => $localStatus,
                    'mercadopago_payment_id' => $paymentId
                ]);
                
                Log::info('Status do pedido atualizado para: ' . $localStatus);
            }
            
            Log::info('=== PAGAMENTO PROCESSADO COM SUCESSO ===');
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar pagamento: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Tipo de exceção: ' . get_class($e));
        }
    }
    
    /**
     * Processa uma notificação de ordem do Mercado Pago
     */
    private function processOrder($orderId)
    {
        try {
            Log::info('Processando ordem ID: ' . $orderId);
            
            // Obter detalhes da ordem
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.mercadopago.com/v1/orders/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $orderData = json_decode($response->getBody(), true);
            Log::info('Detalhes da ordem: ' . json_encode($orderData));
            
            // Verificar se há um external_reference
            if (empty($orderData['external_reference'])) {
                Log::warning('Ordem sem external_reference: ' . $orderId);
                return;
            }
            
            // Encontrar o pedido correspondente
            $externalReference = $orderData['external_reference'];
            $localOrderId = $externalReference;
            if (strpos($externalReference, 'order_') === 0) {
                $localOrderId = substr($externalReference, 6);
            }
            
            $order = Order::find($localOrderId);
            if (!$order) {
                Log::warning('Pedido local não encontrado para a ordem: ' . $orderId . ', external_reference: ' . $externalReference);
                return;
            }
            
            // Atualizar o ID da ordem no pedido local
            $order->update([
                'mercadopago_order_id' => $orderId
            ]);
            
            Log::info('Pedido local atualizado com o ID da ordem: ' . $orderId);
            
            // Verificar se há pagamentos associados à ordem
            if (isset($orderData['transactions']) && isset($orderData['transactions']['payments']) && count($orderData['transactions']['payments']) > 0) {
                foreach ($orderData['transactions']['payments'] as $payment) {
                    if (isset($payment['id'])) {
                        Log::info('Processando pagamento associado à ordem: ' . $payment['id']);
                        $this->processPayment($payment['id']);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar ordem: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Encontra um pedido local com base em vários identificadores
     */
    private function findOrder($identifiers)
    {
        $order = null;
        
        // Tentar encontrar pelo external_reference
        if (!empty($identifiers['external_reference'])) {
            $externalRef = $identifiers['external_reference'];
            $localOrderId = $externalRef;
            
            if (strpos($externalRef, 'order_') === 0) {
                $localOrderId = substr($externalRef, 6);
            }
            
            $order = Order::find($localOrderId);
            if ($order) {
                Log::info('Pedido encontrado pelo external_reference: ' . $externalRef);
                return $order;
            }
        }
        
        // Tentar encontrar pelo payment_id
        if (!empty($identifiers['payment_id'])) {
            $order = Order::where('mercadopago_payment_id', $identifiers['payment_id'])->first();
            if ($order) {
                Log::info('Pedido encontrado pelo payment_id: ' . $identifiers['payment_id']);
                return $order;
            }
        }
        
        // Tentar encontrar pelo preference_id
        if (!empty($identifiers['preference_id'])) {
            $order = Order::where('mercadopago_preference_id', $identifiers['preference_id'])->first();
            if ($order) {
                Log::info('Pedido encontrado pelo preference_id: ' . $identifiers['preference_id']);
                return $order;
            }
        }
        
        // Tentar encontrar pelo order_id
        if (!empty($identifiers['order_id'])) {
            $order = Order::where('mercadopago_order_id', $identifiers['order_id'])->first();
            if ($order) {
                Log::info('Pedido encontrado pelo order_id: ' . $identifiers['order_id']);
                return $order;
            }
        }
        
        return null;
    }
    
    /**
     * Aprova um pedido e cria a compra para o usuário
     */
    private function approveOrder($order, $payment)
    {
        try {
            Log::info('Aprovando pedido: ' . $order->id);
            
            // Determinar método de pagamento
            $paymentMethod = $this->determinePaymentMethod(
                $payment->payment_method_id ?? '',
                $payment->payment_type_id ?? ''
            );
            
            Log::info('Método de pagamento determinado: ' . $paymentMethod);
            
            // Preparar dados do comprador
            $payerData = [];
            if (isset($payment->payer)) {
                $payerData = [
                    'payer_first_name' => $payment->payer->first_name ?? null,
                    'payer_last_name' => $payment->payer->last_name ?? null,
                    'payer_email' => $payment->payer->email ?? null
                ];
                
                // Adicionar dados de identificação se disponíveis
                if (isset($payment->payer->identification)) {
                    $payerData['payer_identification_type'] = $payment->payer->identification->type ?? null;
                    $payerData['payer_identification_number'] = $payment->payer->identification->number ?? null;
                }
                
                Log::info('Dados do comprador para atualização:', $payerData);
            }
            
            // Atualizar o pedido
            $order->update(array_merge([
                'status' => 'approved',
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'mercadopago_payment_id' => $payment->id ?? $order->mercadopago_payment_id
            ], $payerData));
            
            // Criar a compra para o usuário
            $purchase = $this->createUserPurchase($order->user, $order->digitalProduct);
            
            Log::info('Pedido aprovado e compra criada', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'product_id' => $order->digital_product_id,
                'purchase_id' => $purchase ? $purchase->id : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao aprovar pedido: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Rota de teste para webhook do Mercado Pago
     * Esta rota é usada para testar o recebimento de notificações do Mercado Pago
     * sem a necessidade de configurar um servidor público
     */
    public function testWebhook(Request $request)
    {
        // Criar um arquivo de log específico para webhooks de teste
        $logFile = storage_path('logs/webhook_test_' . date('Y-m-d') . '.log');
        
        // Registrar informações completas da requisição
        $logData = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'headers' => $request->header(),
            'body' => $request->all(),
        ];
        
        // Escrever no arquivo de log
        file_put_contents(
            $logFile, 
            json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", 
            FILE_APPEND
        );
        
        // Processar a notificação normalmente
        if (isset($request->action) && $request->action === 'payment.created' && isset($request->data['id'])) {
            // Simular processamento de pagamento
            Log::info('Test Webhook - Simulando processamento de pagamento: ' . $request->data['id']);
        }
        
        // Sempre retornar 200 OK
        return response()->json([
            'status' => 'ok',
            'message' => 'Webhook de teste recebido e processado com sucesso',
            'received_data' => $request->all()
        ]);
    }

    private function determinePaymentMethod($paymentMethodId, $paymentTypeId)
    {
        if (strpos($paymentMethodId, 'pix') !== false || strpos($paymentTypeId, 'pix') !== false) {
            return 'pix';
        } elseif (strpos($paymentMethodId, 'bolbradesco') !== false || strpos($paymentTypeId, 'ticket') !== false) {
            return 'boleto';
        } elseif (in_array($paymentTypeId, ['credit_card', 'debit_card']) || 
                 in_array($paymentMethodId, ['visa', 'master', 'elo', 'amex', 'hipercard'])) {
            return 'credit_card';
        } elseif (strpos($paymentMethodId, 'transfer') !== false || strpos($paymentTypeId, 'transfer') !== false) {
            return 'transfer';
        } else {
            return $paymentMethodId ?: 'unknown';
        }
    }

    private function createUserPurchase($user, $product)
    {
        Log::info('=== CRIANDO COMPRA DO USUÁRIO ===');
        Log::info('Usuário: ' . $user->email);
        Log::info('Produto: ' . $product->title . ' (ID: ' . $product->id . ')');
        Log::info('Já possui o produto: ' . ($user->hasPurchased($product->id) ? 'Sim' : 'Não'));
        
        // Verificar se já não possui o produto
        if (!$user->hasPurchased($product->id)) {
            $purchase = $user->purchases()->create([
                'digital_product_id' => $product->id,
                'purchased_at' => now()
            ]);
            
            Log::info('Compra criada com ID: ' . $purchase->id);
            return $purchase;
        } else {
            Log::info('Usuário já possui o produto. Compra não criada.');
            return null;
        }
    }

    /**
     * Atualizar métodos de pagamento dos pedidos existentes
     * Este método pode ser chamado via Artisan ou via rota administrativa
     */
    public function updateExistingOrderPaymentMethods()
    {
        try {
            Log::info('=== ATUALIZANDO MÉTODOS DE PAGAMENTO DOS PEDIDOS EXISTENTES ===');
            
            $orders = Order::whereNull('payment_method')->get();
            $updatedCount = 0;
            
            foreach ($orders as $order) {
                $paymentMethod = $this->determinePaymentMethodFromOrder($order);
                
                if ($paymentMethod) {
                    $order->update(['payment_method' => $paymentMethod]);
                    $updatedCount++;
                    Log::info("Pedido {$order->id} atualizado para {$paymentMethod}");
                }
            }
            
            Log::info("✅ {$updatedCount} pedidos foram atualizados");
            
            return response()->json([
                'success' => true,
                'message' => "✅ {$updatedCount} pedidos foram atualizados com sucesso!",
                'updated_count' => $updatedCount
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao atualizar métodos de pagamento: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar métodos de pagamento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Determinar método de pagamento baseado nos dados do pedido
     */
    private function determinePaymentMethodFromOrder($order)
    {
        // Se tem payment_id, buscar informações do Mercado Pago
        if ($order->mercadopago_payment_id) {
            try {
                $client = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $client->get($order->mercadopago_payment_id);
                
                $mpPaymentMethodId = $payment->payment_method_id ?? '';
                $mpPaymentTypeId = $payment->payment_type_id ?? '';
                
                Log::info("Pedido {$order->id} - Payment Method ID: {$mpPaymentMethodId}");
                Log::info("Pedido {$order->id} - Payment Type ID: {$mpPaymentTypeId}");
                
                // Mapear métodos do Mercado Pago
                if (strpos($mpPaymentMethodId, 'pix') !== false || strpos($mpPaymentTypeId, 'pix') !== false) {
                    return 'pix';
                } elseif (strpos($mpPaymentMethodId, 'bolbradesco') !== false || strpos($mpPaymentTypeId, 'ticket') !== false) {
                    return 'boleto';
                } elseif (in_array($mpPaymentTypeId, ['credit_card', 'debit_card']) || 
                         in_array($mpPaymentMethodId, ['visa', 'master', 'elo', 'amex', 'hipercard'])) {
                    return 'credit_card';
                } elseif (strpos($mpPaymentMethodId, 'transfer') !== false || strpos($mpPaymentTypeId, 'transfer') !== false) {
                    return 'transfer';
                } else {
                    return $mpPaymentMethodId ?: 'unknown';
                }
                
            } catch (Exception $e) {
                Log::error("Erro ao obter informações do pagamento {$order->mercadopago_payment_id}: " . $e->getMessage());
            }
        }
        
        // Se tem preference_id, verificar se é PIX
        if ($order->mercadopago_preference_id) {
            try {
                $client = new PreferenceClient();
                $preference = $client->get($order->mercadopago_preference_id);
                
                // Verificar se há restrições de método de pagamento na preferência
                if (isset($preference->payment_methods)) {
                    if (isset($preference->payment_methods->excluded_payment_types)) {
                        foreach ($preference->payment_methods->excluded_payment_types as $excluded) {
                            if ($excluded->id === 'credit_card' || $excluded->id === 'debit_card') {
                                return 'pix'; // Se cartão foi excluído, provavelmente é PIX
                            }
                        }
                    }
                }
                
            } catch (Exception $e) {
                Log::error("Erro ao obter informações da preferência {$order->mercadopago_preference_id}: " . $e->getMessage());
            }
        }
        
        // Se não conseguir determinar, usar padrão baseado no status
        if ($order->status === 'approved') {
            return 'credit_card'; // Assumir cartão para pedidos aprovados
        } elseif ($order->status === 'pending') {
            return 'credit_card'; // Assumir cartão para pedidos pendentes
        }
        
        return 'unknown';
    }

    /**
     * Criar pagamento de cartão de crédito via API de Payments
     */
    private function createCreditCardPayment(Request $request, $product, $order, $user, $accessToken)
    {
        try {
            Log::info('=== CRIANDO PAGAMENTO DE CARTÃO DE CRÉDITO ===');
            
            // Verificar se estamos em ambiente local
            $isLocal = in_array(request()->getHost(), ['127.0.0.1', 'localhost', '127.0.0.1:8000', 'localhost:8000']);
            
            $payload = [
                'description' => 'Pagamento com cartão para ' . $product->title,
                'external_reference' => 'order_' . $order->id,
                'payer' => [
                    'email' => $user->email,
                    'first_name' => $this->ensureString($request->input('card_holder_name', 'Cliente')),
                    'last_name' => '',
                    'entity_type' => 'individual',
                    'identification' => [
                        'type' => $this->ensureString($request->input('identification_type', 'CPF')),
                        'number' => preg_replace('/\D/', '', $this->ensureString($request->input('card_holder_doc', ''))) // Remove caracteres não numéricos
                    ]
                ],
                'payment_method_id' => $this->ensureString($request->input('card_brand', 'visa')),
                'transaction_amount' => (float) $product->price,
                'installments' => (int) $request->input('installments', 1)
            ];
            
            // Adicionar token do cartão se fornecido
            if ($request->filled('card_token')) {
                $cardToken = $this->ensureString($request->input('card_token'));
                
                // Se for um token de teste, garantir que tenha pelo menos 32 caracteres
                if (strlen($cardToken) < 32) {
                    $cardToken = str_pad($cardToken, 32, '0', STR_PAD_RIGHT);
                    Log::info('Token ajustado para 32 caracteres: ' . $cardToken);
                }
                
                $payload['token'] = $cardToken;
                Log::info('Token do cartão incluído: ' . $cardToken);
            }
            
            // Adicionar dados de identificação se fornecidos
            if ($request->filled('card_holder_doc')) {
                $cardHolderDoc = $this->ensureString($request->input('card_holder_doc'));
                
                $payload['payer']['identification'] = [
                    'type' => 'CPF',
                    'number' => preg_replace('/\D/', '', $cardHolderDoc)
                ];
            }
            
            // Só incluir notification_url se for uma URL pública válida
            if (!$isLocal) {
                $payload['notification_url'] = url('/api/webhooks/mercadopago');
                Log::info('Notification URL incluída: ' . $payload['notification_url']);
            } else {
                Log::info('Notification URL não incluída (ambiente local)');
            }
            
            Log::info('Payload para API de Payments (Cartão):', $payload);
            
            // Fazer a requisição para a API de pagamentos
            $client = new \GuzzleHttp\Client();
            
            // Gerar um ID de idempotência único
            $idempotencyKey = md5('card_' . $order->id . '_' . time() . '_' . rand(1000, 9999));
            
            Log::info('Fazendo requisição para API de Payments com ID de idempotência: ' . $idempotencyKey);
            
            $response = $client->post('https://api.mercadopago.com/v1/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey
                ],
                'json' => $payload,
                'timeout' => 30
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            Log::info('Resposta da API de Payments (Cartão):', $responseData);
            
            if (isset($responseData['id'])) {
                // Atualizar o pedido com o ID do pagamento
                $order->update([
                    'mercadopago_payment_id' => $responseData['id'],
                    'mercadopago_order_id' => $responseData['id'], // Para compatibilidade
                    'status' => $responseData['status']
                ]);
                
                Log::info('Pedido atualizado com payment_id: ' . $responseData['id']);
                
                // Verificar se o pagamento foi realmente aprovado
                $paymentStatus = $responseData['status'];
                $statusDetail = $responseData['status_detail'] ?? '';
                
                // Considerar como sucesso: approved, in_process, ou pending (exceto rejeitados)
                $isSuccess = in_array($paymentStatus, ['approved', 'in_process', 'pending']) && 
                             !in_array($statusDetail, ['cc_rejected_high_risk', 'cc_rejected_bad_filled_security_code', 'cc_rejected_bad_filled_date', 'cc_rejected_bad_filled_other']);
                
                // Log do status do pagamento
                Log::info('Status do pagamento retornado: ' . $paymentStatus);
                Log::info('Status detail: ' . $statusDetail);
                Log::info('Pagamento aceito: ' . ($isSuccess ? 'Sim' : 'Não'));
                
                // Se o pagamento foi aceito, liberar o produto imediatamente
                if ($isSuccess) {
                    Log::info('Pagamento aceito - liberando produto imediatamente para o usuário: ' . $user->email);
                    
                    // Atualizar status do pedido para aprovado
                    $order->update([
                        'status' => 'approved',
                        'paid_at' => now(),
                        'payment_method' => 'credit_card'
                    ]);
                    
                    // Criar compra do usuário (liberar produto)
                    $this->createUserPurchase($user, $order->digitalProduct);
                    
                    Log::info('Produto liberado com sucesso para o usuário: ' . $user->email);
                }
                
                // Preparar mensagem de erro mais clara
                $errorMessage = $this->getErrorMessage($paymentStatus, $statusDetail);
                
                // Retornar dados do pagamento com status correto
                return response()->json([
                    'success' => $isSuccess,
                    'payment_id' => $responseData['id'],
                    'status' => $paymentStatus,
                    'status_detail' => $statusDetail,
                    'external_reference' => $responseData['external_reference'],
                    'payment_details' => $responseData,
                    'message' => $isSuccess ? 
                        'Pagamento aceito e produto liberado!' : 
                        $errorMessage
                ]);
            } else {
                Log::error('Resposta da API não contém ID do pagamento');
                return response()->json([
                    'success' => false,
                    'error' => 'Resposta da API não contém ID do pagamento'
                ], 500);
            }
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = (string) $e->getResponse()->getBody();
            $statusCode = $e->getResponse()->getStatusCode();
            
            Log::error('Erro HTTP na API de Payments (Cartão): ' . $statusCode);
            Log::error('Body da resposta: ' . $body);
            Log::error('Headers da resposta: ' . json_encode($e->getResponse()->getHeaders()));
            
            return response()->json([
                'success' => false,
                'error' => 'Erro na API do Mercado Pago: ' . $statusCode,
                'details' => json_decode($body, true),
                'status_code' => $statusCode
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento de cartão: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Verifica se o cartão é um cartão de teste válido
     * Em produção, cartões de teste devem ser rejeitados
     */
    private function isValidTestCard($payment)
    {
        try {
            // Verificar se estamos em modo de produção
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            
            // Se o token começa com APP, estamos em produção
            if (str_starts_with($accessToken, 'APP')) {
                Log::info('Modo PRODUÇÃO detectado - rejeitando cartões de teste');
                return false; // Em produção, cartões de teste são inválidos
            }
            
            // Se o token começa com TEST, estamos em modo de teste
            if (str_starts_with($accessToken, 'TEST')) {
                Log::info('Modo TESTE detectado - permitindo cartões de teste');
                return true; // Em teste, cartões de teste são válidos
            }
            
            // Se não conseguir identificar o modo, por segurança, rejeitar
            Log::warning('Não foi possível identificar o modo do token - rejeitando por segurança');
            return false;
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar se é cartão de teste: ' . $e->getMessage());
            // Em caso de erro, por segurança, rejeitar
            return false;
        }
    }

    /**
     * Mapeia os status do Mercado Pago para status locais
     */
    private function mapMercadoPagoStatus($mpStatus, $mpStatusDetail = null)
    {
        Log::info("Mapeando status do Mercado Pago: {$mpStatus}, detail: {$mpStatusDetail}");
        
        switch ($mpStatus) {
            case 'approved':
                return 'approved';
                
            case 'rejected':
                return 'rejected';
                
            case 'pending':
                // Verificar detalhes para determinar o tipo de pendência
                if ($mpStatusDetail === 'pending_review_manual') {
                    return 'pending_review';
                } elseif ($mpStatusDetail === 'pending_contingency') {
                    return 'pending_contingency';
                } else {
                    return 'pending';
                }
                
            case 'in_process':
                return 'processing';
                
            case 'cancelled':
                return 'cancelled';
                
            case 'refunded':
                return 'refunded';
                
            case 'charged_back':
                return 'charged_back';
                
            default:
                Log::warning("Status do Mercado Pago não reconhecido: {$mpStatus}");
                return 'unknown';
        }
    }

    private function ensureString($value)
    {
        if (is_array($value)) {
            return implode('', $value);
        } elseif (is_string($value)) {
            return $value;
        } else {
            throw new \InvalidArgumentException("Invalid format for string conversion");
        }
    }

    private function getErrorMessage($paymentStatus, $statusDetail)
    {
        // Mensagens específicas para cada tipo de erro
        switch ($statusDetail) {
            case 'cc_rejected_high_risk':
                return 'Cartão rejeitado por alto risco. Possíveis causas: dados inconsistentes, CPF/CNPJ incorreto, ou restrições de segurança. Verifique os dados informados.';
            
            case 'cc_rejected_bad_filled_security_code':
                return 'Código de segurança (CVV) incorreto. Verifique o número de 3 ou 4 dígitos no verso do cartão.';
            
            case 'cc_rejected_bad_filled_date':
                return 'Data de validade incorreta. Verifique o mês e ano de expiração do cartão.';
            
            case 'cc_rejected_bad_filled_other':
                return 'Dados do cartão incorretos. Verifique: número do cartão, nome do titular, CPF/CNPJ e endereço.';
            
            case 'cc_rejected_insufficient_amount':
                return 'Saldo insuficiente no cartão. Verifique o limite disponível.';
            
            case 'cc_rejected_max_attempts':
                return 'Número máximo de tentativas excedido. Tente novamente em algumas horas.';
            
            case 'cc_rejected_duplicated_payment':
                return 'Pagamento duplicado detectado. Este pagamento já foi processado.';
            
            default:
                if ($paymentStatus === 'rejected') {
                    return 'Cartão rejeitado: ' . ($statusDetail ?: 'motivo não especificado') . '. Verifique os dados ou entre em contato com o suporte.';
                }
                return 'Erro no pagamento: ' . ($statusDetail ?: $paymentStatus) . '. Tente novamente ou entre em contato com o suporte.';
        }
    }

    /**
     * Verifica o status de um pagamento via API
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $paymentId = $request->input('payment_id');
            
            Log::info('=== VERIFICANDO STATUS DO PAGAMENTO ===');
            Log::info('Order ID: ' . $orderId);
            Log::info('Payment ID: ' . $paymentId);
            
            // Buscar o pedido
            $order = Order::find($orderId);
            if (!$order) {
                Log::warning('Pedido não encontrado: ' . $orderId);
                return response()->json([
                    'success' => false,
                    'error' => 'Pedido não encontrado'
                ], 404);
            }
            
            // Se temos payment_id, verificar no Mercado Pago
            if ($paymentId) {
                $client = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $client->get($paymentId);
                
                Log::info('Status do pagamento no Mercado Pago: ' . ($payment->status ?? 'unknown'));
                Log::info('Status detail: ' . ($payment->status_detail ?? 'não informado'));
                
                return response()->json([
                    'success' => true,
                    'status' => $payment->status,
                    'status_detail' => $payment->status_detail,
                    'order_status' => $order->status,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId
                ]);
            }
            
            // Se não temos payment_id, retornar status do pedido
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'order_id' => $orderId,
                'message' => 'Status obtido do pedido local'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do pagamento: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }
}
