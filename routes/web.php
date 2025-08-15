<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    // Buscar configurações de página inicial
    $generalSettings = \App\Models\Setting::getGeneralSettings();
    
    // Se a página inicial estiver desabilitada, mostrar página de boas-vindas
    if ($generalSettings['homepage_enabled'] !== '1') {
        return view('welcome');
    }
    
    // Se o tipo for 'custom' e tiver URL, redirecionar
    if ($generalSettings['homepage_type'] === 'custom' && !empty($generalSettings['homepage_url'])) {
        return redirect()->away($generalSettings['homepage_url']);
    }
    
    // Se o tipo for 'login' ou se for 'custom' mas sem URL, redirecionar para login
    if ($generalSettings['homepage_type'] === 'login' || 
        ($generalSettings['homepage_type'] === 'custom' && empty($generalSettings['homepage_url']))) {
        return redirect('/login');
    }
    
    // Fallback: mostrar página de boas-vindas
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
    
    // Obter configurações de marca e aparência
    $brandingSettings = \App\Models\Setting::getBrandingSettings();
    $appearanceSettings = \App\Models\Setting::getAppearanceSettings();
    
    // Calcular cores RGB e gradiente
    $primaryColor = $appearanceSettings['primary_color'];
    $secondaryColor = $appearanceSettings['secondary_color'];
    $backgroundColor = $appearanceSettings['background_color'];
    $cardBackground = $appearanceSettings['card_background'];
    
    // Converter hex para rgba para box-shadow
    $primaryColorRgba = hexToRgba($primaryColor, 0.25);
    
    // Criar gradiente de fundo
    $backgroundGradient = adjustBrightness($backgroundColor, 20);
    
    return view('auth.login', [
        'siteName' => $brandingSettings['site_name'],
        'logoPath' => $brandingSettings['logo_path'],
        'faviconPath' => $brandingSettings['favicon_path'],
        'primaryColor' => $primaryColor,
        'secondaryColor' => $secondaryColor,
        'backgroundColor' => $backgroundColor,
        'cardBackground' => $cardBackground,
        'primaryColorRgba' => $primaryColorRgba,
        'backgroundGradient' => $backgroundGradient,
    ]);
})->name('login');

// Função helper para converter hex para rgba
function hexToRgba($hex, $alpha = 1) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "rgba($r, $g, $b, $alpha)";
}

// Função helper para ajustar brilho
function adjustBrightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

Route::post('/login', function (Request $request) {
    // Verificar se é requisição JSON
    if ($request->isJson()) {
        // Decodificar dados se estiverem em base64
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->boolean('remember', false);
        
        // Verificar se os dados estão codificados em base64
        if (base64_encode(base64_decode($email, true)) === $email) {
            $email = base64_decode($email);
        }
        if (base64_encode(base64_decode($password, true)) === $password) {
            $password = base64_decode($password);
        }

        // Validar dados decodificados
        $validator = \Illuminate\Support\Facades\Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email|max:255',
            'password' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.'
            ], 400);
        }

        // Rate limiting para login
        $key = 'login_attempts:' . $request->ip();
        if (\Illuminate\Support\Facades\Cache::get($key, 0) >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas tentativas. Tente novamente em alguns minutos.'
            ], 429);
        }

        $credentials = ['email' => $email, 'password' => $password];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Limpar tentativas de login em caso de sucesso
            \Illuminate\Support\Facades\Cache::forget($key);
            
            return redirect()->intended('/dashboard');
        }

        // Incrementar tentativas em caso de falha
        \Illuminate\Support\Facades\Cache::increment($key);
        \Illuminate\Support\Facades\Cache::put($key, \Illuminate\Support\Facades\Cache::get($key), 300); // 5 minutos

        return response()->json([
            'success' => false,
            'message' => 'As credenciais fornecidas não correspondem aos nossos registros.'
        ], 401);
    }

    // Fallback para form tradicional (caso JavaScript falhe)
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $remember = $request->boolean('remember', false);

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
    ])->withInput($request->only('email', 'remember'));
})->name('login.post');

// Rotas de recuperação de senha
Route::post('/password/email', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Este email não está cadastrado em nosso sistema.'
        ], 404);
    }

    // Gerar código de 6 dígitos
    $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    // Invalidar códigos anteriores para este email
    \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->update(['used' => true]);
    
    // Armazenar novo código
    \Illuminate\Support\Facades\DB::table('password_reset_codes')->insert([
        'email' => $request->email,
        'code' => $code,
        'expires_at' => now()->addMinutes(15), // Código expira em 15 minutos
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Enviar email com código (implementar provedor de email em produção)
    try {
        // Aqui você implementaria o envio do email real
        // Por exemplo: Mail::to($user)->send(new PasswordResetCodeMail($code));
        
        return response()->json([
            'success' => true,
            'message' => 'Código de recuperação enviado para seu email! Válido por 15 minutos.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar email. Tente novamente.'
        ], 500);
    }
})->name('password.email');

// Rota para validar código e permitir redefinição
Route::post('/password/validate-code', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string|size:6',
    ]);

    $resetCode = \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $request->email)
        ->where('code', $request->code)
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->first();

    if (!$resetCode) {
        return response()->json([
            'success' => false,
            'message' => 'Código inválido ou expirado.'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'message' => 'Código válido! Agora você pode redefinir sua senha.'
    ]);
})->name('password.validate-code');

// Rota para redefinir senha com código
Route::post('/password/reset-with-code', function (Request $request) {
    // Decodificar dados se estiverem em base64
    $email = $request->email;
    $password = $request->password;
    $passwordConfirmation = $request->password_confirmation;
    
    // Verificar se os dados estão codificados em base64
    if (base64_encode(base64_decode($email, true)) === $email) {
        $email = base64_decode($email);
    }
    if (base64_encode(base64_decode($password, true)) === $password) {
        $password = base64_decode($password);
    }
    if (base64_encode(base64_decode($passwordConfirmation, true)) === $passwordConfirmation) {
        $passwordConfirmation = base64_decode($passwordConfirmation);
    }

    // Validar dados decodificados
    $validator = \Illuminate\Support\Facades\Validator::make([
        'email' => $email,
        'code' => $request->code,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
    ], [
        'email' => 'required|email|max:255',
        'code' => 'required|string|size:6',
        'password' => 'required|confirmed|min:8|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos.'
        ], 400);
    }

    // Rate limiting para redefinição de senha
    $key = 'password_reset:' . request()->ip();
    if (\Illuminate\Support\Facades\Cache::get($key, 0) >= 3) {
        return response()->json([
            'success' => false,
            'message' => 'Muitas tentativas. Tente novamente em alguns minutos.'
        ], 429);
    }
    
    \Illuminate\Support\Facades\Cache::increment($key);
    \Illuminate\Support\Facades\Cache::put($key, \Illuminate\Support\Facades\Cache::get($key), 60);

    // Verificar código
    $resetCode = \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('email', $email)
        ->where('code', $request->code)
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->first();

    if (!$resetCode) {
        return response()->json([
            'success' => false,
            'message' => 'Código inválido ou expirado.'
        ], 400);
    }

    // Atualizar senha do usuário
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não encontrado.'
        ], 404);
    }

    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->save();

    // Marcar código como usado
    \Illuminate\Support\Facades\DB::table('password_reset_codes')
        ->where('id', $resetCode->id)
        ->update(['used' => true]);

    return response()->json([
        'success' => true,
        'message' => 'Senha redefinida com sucesso!'
    ]);
})->name('password.reset-with-code');

// Rotas para validação em tempo real (com proteções de segurança)
Route::post('/validate/email', function (Request $request) {
    $request->validate([
        'email' => 'required|email|max:255',
    ]);

    // Rate limiting básico: máximo 10 tentativas por minuto por IP
    $key = 'email_validation:' . $request->ip();
    if (\Illuminate\Support\Facades\Cache::get($key, 0) >= 10) {
        return response()->json([
            'valid' => false,
            'message' => 'Muitas tentativas. Tente novamente em alguns minutos.'
        ], 429);
    }
    
    \Illuminate\Support\Facades\Cache::increment($key);
    \Illuminate\Support\Facades\Cache::put($key, \Illuminate\Support\Facades\Cache::get($key), 60);

    // Validação apenas de formato - NÃO revela se email existe
    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $isValidFormat = preg_match($emailRegex, $request->email);
    
    // Simular delay para mascarar timing attacks
    usleep(rand(100000, 300000)); // 100-300ms
    
    return response()->json([
        'valid' => $isValidFormat,
        'message' => $isValidFormat ? 'Formato válido' : 'Formato de email inválido'
    ])->withHeaders([
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY'
    ]);
})->name('validate.email');

Route::post('/validate/credentials', function (Request $request) {
    $request->validate([
        'email' => 'required|email|max:255',
        'password' => 'required|min:1|max:255',
    ]);

    // Rate limiting mais rigoroso para credenciais: 5 tentativas por minuto por IP
    $key = 'credential_validation:' . $request->ip();
    if (\Illuminate\Support\Facades\Cache::get($key, 0) >= 5) {
        return response()->json([
            'valid' => false,
            'message' => 'Muitas tentativas. Tente novamente em alguns minutos.'
        ], 429);
    }
    
    \Illuminate\Support\Facades\Cache::increment($key);
    \Illuminate\Support\Facades\Cache::put($key, \Illuminate\Support\Facades\Cache::get($key), 60);

    // Simular validação sem expor informações reais
    // Em produção, remova esta funcionalidade ou implemente com muito cuidado
    usleep(rand(200000, 500000)); // 200-500ms para mascarar timing
    
    return response()->json([
        'valid' => false, // Sempre retorna false por segurança
        'message' => 'Validação desabilitada por segurança'
    ])->withHeaders([
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY'
    ]);
})->name('validate.credentials');

Route::get('/password/reset/{token}', function ($token, Request $request) {
    $email = $request->get('email');
    
    if (!$email) {
        return redirect('/login')->with('error', 'Link de recuperação inválido.');
    }

    // Verificar se o token é válido
    $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $email)
        ->first();

    if (!$resetRecord || !\Illuminate\Support\Facades\Hash::check($token, $resetRecord->token)) {
        return redirect('/login')->with('error', 'Link de recuperação inválido ou expirado.');
    }

    // Verificar se o token não expirou (24 horas)
    if (now()->diffInHours($resetRecord->created_at) > 24) {
        return redirect('/login')->with('error', 'Link de recuperação expirado.');
    }

    // Obter configurações para a view
    $brandingSettings = \App\Models\Setting::getBrandingSettings();
    $appearanceSettings = \App\Models\Setting::getAppearanceSettings();
    
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $email,
        'siteName' => $brandingSettings['site_name'],
        'logoPath' => $brandingSettings['logo_path'],
        'faviconPath' => $brandingSettings['favicon_path'],
        'primaryColor' => $appearanceSettings['primary_color'],
        'secondaryColor' => $appearanceSettings['secondary_color'],
        'backgroundColor' => $appearanceSettings['background_color'],
        'cardBackground' => $appearanceSettings['card_background'],
        'primaryColorRgba' => hexToRgba($appearanceSettings['primary_color'], 0.25),
        'backgroundGradient' => adjustBrightness($appearanceSettings['background_color'], 20),
    ]);
})->name('password.reset');

Route::post('/password/reset', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:8',
    ]);

    // Verificar token
    $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$resetRecord || !\Illuminate\Support\Facades\Hash::check($request->token, $resetRecord->token)) {
        return back()->withErrors(['email' => 'Token de recuperação inválido.']);
    }

    // Verificar se não expirou
    if (now()->diffInHours($resetRecord->created_at) > 24) {
        return back()->withErrors(['email' => 'Token de recuperação expirado.']);
    }

    // Atualizar senha do usuário
    $user = \App\Models\User::where('email', $request->email)->first();
    if (!$user) {
        return back()->withErrors(['email' => 'Usuário não encontrado.']);
    }

    $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
    $user->save();

    // Remover token usado
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return redirect('/login')->with('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
})->name('password.update');

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
    
    // Rotas para integração com Mercado Pago (protegidas por autenticação)
    Route::post('/payment/create-order', [PaymentController::class, 'createMercadoPagoOrder'])->name('payment.create-order');
    
    // Rota específica para PIX (protegida por autenticação, sem CSRF para evitar problemas)
    Route::post('/payment/generate-pix', [PaymentController::class, 'generatePixQRCode'])
        ->name('payment.generate-pix')
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    
    // Rota para verificar status do pagamento (protegida por autenticação)
    Route::post('/api/payment/check-status', [PaymentController::class, 'checkPaymentStatus'])
        ->name('api.payment.check-status');
});

// Rotas de pagamento (sem autenticação para webhook e retorno)
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failure', [PaymentController::class, 'failure'])->name('payment.failure');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');





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
    Route::post('/settings/general', [AdminController::class, 'updateGeneralSettings'])->name('settings.general');
    Route::post('/settings/mercadopago', [AdminController::class, 'updateMercadoPagoSettings'])->name('settings.mercadopago');
    Route::post('/settings/appearance', [AdminController::class, 'updateAppearanceSettings'])->name('settings.appearance');
    Route::post('/settings/branding', [AdminController::class, 'updateBrandingSettings'])->name('settings.branding');
    Route::delete('/settings/logo', [AdminController::class, 'removeLogo'])->name('settings.logo.remove');
    Route::delete('/settings/favicon', [AdminController::class, 'removeFavicon'])->name('settings.favicon.remove');
    
    // Ferramentas do Sistema
    Route::post('/tools/clear-cache', [AdminController::class, 'clearCache'])->name('tools.clear-cache');
    Route::get('/tools/backup', [AdminController::class, 'downloadBackup'])->name('tools.backup');
    Route::get('/tools/logs', [AdminController::class, 'getSystemLogs'])->name('tools.logs');
    
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
