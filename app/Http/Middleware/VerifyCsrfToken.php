<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Rotas de webhook do Mercado Pago
        'api/webhooks/mercadopago',
        'api/webhook',
        'webhook',
        'payment/webhook',
        'webhooks/mercadopago', // Rota sem prefixo /api
        '/webhooks/mercadopago', // Com barra inicial
        '/webhook', // Com barra inicial
        '/api/webhook', // Com barra inicial
        '/payment/webhook', // Com barra inicial
        'api/test-webhook', // Rota de teste
        '/api/test-webhook', // Rota de teste com barra inicial
        'test-webhook', // Rota de teste sem prefixo
        '/test-webhook', // Rota de teste sem prefixo com barra inicial
        'payment/generate-pix', // Endpoint PIX 
        '/payment/generate-pix', // Endpoint PIX com barra inicial
    ];
}
