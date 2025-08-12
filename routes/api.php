<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook principal do Mercado Pago - sem verificação CSRF e com middleware específico
Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])
    ->name('api.webhook.mercadopago')
    ->middleware(['webhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Rota alternativa para o webhook (para compatibilidade)
Route::post('/webhook', [PaymentController::class, 'webhook'])
    ->name('api.webhook')
    ->middleware(['webhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    
// Rota de teste para webhook (sem CSRF e sem autenticação)
Route::post('/test-webhook', [PaymentController::class, 'testWebhook'])
    ->name('test.webhook')
    ->middleware(['webhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    
// Rota específica para teste de webhook do Mercado Pago (sem middleware)
Route::post('/mp-webhook', [PaymentController::class, 'testWebhook'])
    ->name('mp.webhook')
    ->middleware([]);

// Rota para gerar PIX (sem CSRF por estar em API)
Route::post('/payment/generate-pix', [PaymentController::class, 'generatePixQRCode'])
    ->name('api.payment.generate-pix');

// Rota para verificar status do pagamento
Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus'])
    ->name('api.payment.check-status');

 