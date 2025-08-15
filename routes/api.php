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
    




 