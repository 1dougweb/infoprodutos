<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return view('test');
});

// Rota de login
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
    ])->withInput($request->only('email'));
})->name('login.post');

// Rota de logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rotas da área de membros (protegidas por autenticação)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [MembershipController::class, 'index'])->name('membership.index');
    Route::get('/membership/download/{id}', [MembershipController::class, 'download'])->name('membership.download');
    Route::get('/dashboard/profile', [MembershipController::class, 'profile'])->name('membership.profile');
    Route::put('/dashboard/profile', [MembershipController::class, 'updateProfile'])->name('membership.profile.update');
    
    // Rotas do sistema de cursos
    Route::get('/course/{id}', [MembershipController::class, 'course'])->name('membership.course');
    Route::get('/course/{productId}/lesson/{lessonId}', [MembershipController::class, 'lesson'])->name('membership.lesson');
    Route::get('/lesson/{lessonId}/download', [MembershipController::class, 'downloadLesson'])->name('membership.lesson.download');
    Route::post('/course/progress', [MembershipController::class, 'updateProgress'])->name('membership.progress.update');
    
    // Rotas para produtos digitais
    Route::get('/digital-product/{id}', [MembershipController::class, 'digitalProduct'])->name('membership.digital.product');
    Route::get('/digital-product/{id}/download', [MembershipController::class, 'downloadDigitalProduct'])->name('membership.digital.download');
    
    // Rotas de comentários
    Route::post('/lesson/{lessonId}/comments', [MembershipController::class, 'storeComment'])->name('membership.comments.store');
    Route::put('/comments/{commentId}', [MembershipController::class, 'updateComment'])->name('membership.comments.update');
    Route::delete('/comments/{commentId}', [MembershipController::class, 'deleteComment'])->name('membership.comments.delete');
    
    // Rota de checkout (protegida por autenticação)
    Route::get('/checkout/{productId}', [PaymentController::class, 'checkout'])->name('payment.checkout');
});

// Rotas de pagamento (sem autenticação para webhook e retorno)
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failure', [PaymentController::class, 'failure'])->name('payment.failure');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');

// Rotas para integração com Mercado Pago
Route::post('/payment/create-order', [PaymentController::class, 'createMercadoPagoOrder'])->name('payment.create-order');

// Rota específica para PIX (sem CSRF para evitar problemas)
Route::post('/payment/generate-pix', [PaymentController::class, 'generatePixQRCode'])
    ->name('payment.generate-pix')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Rota de teste para PIX sem nenhum middleware
Route::post('/test-pix', [PaymentController::class, 'generatePixQRCode'])
    ->name('test.pix')
    ->middleware([]);

// Rota direta para o checkout do Mercado Pago (para uso em botões e links)
Route::get('/direct-checkout/{productId}', function($productId) {
    try {
        // Buscar o produto
        $product = \App\Models\DigitalProduct::findOrFail($productId);
        
        // Buscar o pedido mais recente para este produto e usuário atual
        $order = \App\Models\Order::where('digital_product_id', $product->id)
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();
        
        if ($order && $order->mercadopago_preference_id) {
            // Usar a preferência existente
            return redirect()->away('https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $order->mercadopago_preference_id);
        } else {
            // Se não tiver pedido ou preferência, redirecionar para o checkout normal
            return redirect()->route('payment.checkout', $productId);
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro no direct-checkout: ' . $e->getMessage());
        return redirect()->route('payment.checkout', $productId);
    }
})->name('payment.direct-checkout');

// Rota de teste para debug
Route::get('/test-checkout-debug/{productId}', function($productId) {
    return response()->json([
        'message' => 'Rota de teste funcionando',
        'productId' => $productId,
        'auth_check' => auth()->check(),
        'user' => auth()->user(),
        'timestamp' => now()
    ]);
})->name('test.checkout.debug');

// Rota de teste simples
Route::get('/test-simple', function() {
    return response()->json([
        'message' => 'Rota simples funcionando',
        'timestamp' => now()
    ]);
})->name('test.simple');

// Rota de teste do Mercado Pago
Route::get('/test-mercadopago/{productId}', function($productId) {
    try {
        $product = \App\Models\DigitalProduct::findOrFail($productId);
        $accessToken = \App\Models\Setting::get('mercadopago_access_token');
        
        return response()->json([
            'message' => 'Teste do Mercado Pago',
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'price' => $product->price
            ],
            'access_token_exists' => !empty($accessToken),
            'access_token_preview' => $accessToken ? substr($accessToken, 0, 10) . '...' : 'não configurado',
            'timestamp' => now()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
})->name('test.mercadopago');

// Rota de teste do checkout
Route::get('/test-checkout/{productId}', function($productId) {
    try {
        $product = \App\Models\DigitalProduct::findOrFail($productId);
        $accessToken = \App\Models\Setting::get('mercadopago_access_token');
        
        return response()->json([
            'message' => 'Teste do Checkout',
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'price' => $product->price
            ],
            'access_token_exists' => !empty($accessToken),
            'access_token_preview' => $accessToken ? substr($accessToken, 0, 10) . '...' : 'não configurado',
            'checkout_url' => route('payment.checkout', $productId),
            'timestamp' => now()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
})->name('test.checkout');

// NOTA: Webhooks agora são tratados exclusivamente via /api/webhooks/mercadopago
// As rotas antigas /payment/webhook foram removidas para evitar confusão
    
// Rota raiz para webhook (sem CSRF) - REMOVIDA para evitar conflito
// Route::post('/webhook', [PaymentController::class, 'webhook'])
//     ->name('webhook')
//     ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    
// Rota direta para webhook do Mercado Pago (sem CSRF) - REMOVIDA para evitar conflito
// Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])
//     ->name('webhooks.mercadopago')
//     ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Rota de teste para webhook sem CSRF e sem middleware web
Route::post('/test-webhook-direct', [PaymentController::class, 'testWebhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Rota para servir imagens dos produtos (pública)
Route::get('/storage/products/images/{filename}', function ($filename) {
    $path = storage_path('app/public/products/images/' . $filename);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->name('storage.products.images');

// Rotas do Admin (protegidas por autenticação e admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Produtos
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');
    
    // Pedidos
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::delete('/orders/{id}', [AdminController::class, 'deleteOrder'])->name('orders.delete');
    Route::post('/orders/bulk-update', [AdminController::class, 'bulkUpdateOrders'])->name('orders.bulk-update');
    Route::post('/orders/update-payment-methods', [PaymentController::class, 'updateExistingOrderPaymentMethods'])->name('orders.update-payment-methods');
    
    // Usuários
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    
    // Configurações
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings/mercadopago', [AdminController::class, 'updateMercadoPagoSettings'])->name('settings.mercadopago');
    Route::post('/settings/appearance', [AdminController::class, 'updateAppearanceSettings'])->name('settings.appearance');
    Route::post('/settings/branding', [AdminController::class, 'updateBrandingSettings'])->name('settings.branding');
    Route::delete('/settings/logo', [AdminController::class, 'removeLogo'])->name('settings.logo.remove');
    Route::delete('/settings/favicon', [AdminController::class, 'removeFavicon'])->name('settings.favicon.remove');
    
    // Usuários Online
    Route::get('/online-users', [AdminController::class, 'onlineUsers'])->name('online-users');
    
    // Gerenciamento de Cursos
    Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
    Route::get('/courses/{id}/modules', [AdminController::class, 'courseModules'])->name('courses.modules');
    Route::get('/courses/{id}/modules/create', [AdminController::class, 'createModule'])->name('courses.modules.create');
    Route::post('/courses/{id}/modules', [AdminController::class, 'storeModule'])->name('courses.modules.store');
    Route::get('/modules/{id}/edit', [AdminController::class, 'editModule'])->name('modules.edit');
    Route::put('/modules/{id}', [AdminController::class, 'updateModule'])->name('modules.update');
    Route::delete('/modules/{id}', [AdminController::class, 'deleteModule'])->name('modules.delete');
    
    // Gerenciamento de Aulas
    Route::get('/modules/{id}/lessons', [AdminController::class, 'moduleLessons'])->name('modules.lessons');
    Route::get('/modules/{id}/lessons/create', [AdminController::class, 'createLesson'])->name('modules.lessons.create');
    Route::post('/modules/{id}/lessons', [AdminController::class, 'storeLesson'])->name('modules.lessons.store');
    Route::get('/lessons/{id}/edit', [AdminController::class, 'editLesson'])->name('lessons.edit');
    Route::put('/lessons/{id}', [AdminController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/lessons/{id}', [AdminController::class, 'deleteLesson'])->name('lessons.delete');
    
    // Gerenciamento de Banners
    Route::get('/banners', [AdminController::class, 'banners'])->name('banners');
    Route::get('/banners/create', [AdminController::class, 'createBanner'])->name('banners.create');
    Route::post('/banners', [AdminController::class, 'storeBanner'])->name('banners.store');
    Route::get('/banners/{id}/edit', [AdminController::class, 'editBanner'])->name('banners.edit');
    Route::put('/banners/{id}', [AdminController::class, 'updateBanner'])->name('banners.update');
    Route::delete('/banners/{id}', [AdminController::class, 'deleteBanner'])->name('banners.delete');
});
