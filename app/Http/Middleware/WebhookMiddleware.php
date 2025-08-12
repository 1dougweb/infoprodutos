<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Log da requisição para debug
        Log::info('WebhookMiddleware - Requisição recebida', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'is_test' => $request->header('X-Test-Webhook') === 'true'
        ]);
        
        // Verificar se é uma requisição de teste
        if ($request->header('X-Test-Webhook') === 'true') {
            Log::info('WebhookMiddleware - Requisição de teste detectada');
        }
        
        // Permitir a requisição passar sem verificações adicionais
        $response = $next($request);
        
        // Log da resposta para debug
        Log::info('WebhookMiddleware - Resposta enviada', [
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('Content-Type'),
            'content_length' => $response->headers->get('Content-Length')
        ]);
        
        return $response;
    }
}
