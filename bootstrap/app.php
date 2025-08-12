<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \LivePixel\MercadoPago\Providers\MercadoPagoServiceProvider::class,
        \App\Providers\MercadoPagoServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'webhook' => \App\Http\Middleware\WebhookMiddleware::class,
        ]);
        
        // Middleware para atualizar atividade do usuário
        $middleware->append(\App\Http\Middleware\UpdateUserActivity::class);
        
        // Excluir rotas de API do CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/api/*',
            'payment/webhook',
            'payment/generate-pix',
            'payment/create-order',
            'test-pix'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
