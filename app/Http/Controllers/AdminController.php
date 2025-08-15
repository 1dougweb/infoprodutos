<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\DigitalProduct;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_products' => DigitalProduct::count(),
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'approved_orders' => Order::approved()->count(),
            'total_revenue' => Order::approved()->sum('amount'),
        ];

        // Dados para o chart de vendas dos últimos 7 dias
        $salesData = [];
        $salesLabels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->format('D'); // Seg, Ter, Qua, etc.
            $salesLabels[] = $dayName;
            
            $dailySales = Order::approved()
                ->whereDate('paid_at', $date->format('Y-m-d'))
                ->sum('amount');
            
            $salesData[] = $dailySales;
        }

        // Dados para o chart de vendas mensais (últimos 6 meses)
        $monthlyData = [];
        $monthlyLabels = [];
        $monthlyDataPreviousYear = [];
        
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M'); // Jan, Fev, Mar, etc.
            $monthlyLabels[] = $monthName;
            
            // Vendas do mês atual (ano dinâmico)
            $monthlySales = Order::approved()
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');
            
            $monthlyData[] = $monthlySales;
            
            // Vendas do mesmo mês do ano anterior
            $monthlySalesPreviousYear = Order::approved()
                ->whereYear('paid_at', $date->year - 1)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');
            
            $monthlyDataPreviousYear[] = $monthlySalesPreviousYear;
        }

        $recent_orders = Order::with(['user', 'digitalProduct'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'recent_orders', 
            'salesData', 
            'salesLabels', 
            'monthlyData', 
            'monthlyLabels', 
            'monthlyDataPreviousYear',
            'currentYear',
            'previousYear'
        ));
    }

    public function products()
    {
        $products = DigitalProduct::with(['activeModules'])->orderBy('order')->get();
        
        // Calcular total de aulas para cada produto
        foreach ($products as $product) {
            $product->total_lessons = $product->getTotalLessonsCount();
        }
        
        return view('admin.products.index', compact('products'));
    }

    public function createProduct()
    {
        return view('admin.products.create');
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'section' => 'required|string|max:255',
            'product_type' => 'required|in:course,digital',
            'price' => 'nullable|numeric|min:0',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_free' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $data = $request->all();
        
        // Tratar checkboxes
        $data['is_active'] = $request->has('is_active');
        $data['is_free'] = $request->has('is_free');
        
        // Se o produto é gratuito, forçar preço zero
        if ($data['is_free']) {
            $data['price'] = 0;
        }
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('products/images', $imageName, 'public');
            
            $data['image'] = $imagePath;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('products', $fileName, 'public');
            
            $data['file_path'] = $filePath;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        try {
            DigitalProduct::create($data);
            return redirect()->route('admin.products')->with('success', 'Produto criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar produto: ' . $e->getMessage())->withInput();
        }
    }

    public function editProduct($id)
    {
        $product = DigitalProduct::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = DigitalProduct::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'section' => 'required|string|max:255',
            'product_type' => 'required|in:course,digital',
            'price' => 'nullable|numeric|min:0',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_free' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'file' => 'nullable|file|max:10240',
        ]);

        $data = $request->all();
        
        // Tratar checkboxes
        $data['is_active'] = $request->has('is_active');
        $data['is_free'] = $request->has('is_free');
        
        // Se o produto é gratuito, forçar preço zero
        if ($data['is_free']) {
            $data['price'] = 0;
        }
        
        if ($request->hasFile('image')) {
            // Remove imagem antiga se existir
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('products/images', $imageName, 'public');
            
            $data['image'] = $imagePath;
        }

        if ($request->hasFile('file')) {
            // Remove arquivo antigo se existir
            if ($product->file_path && Storage::disk('public')->exists($product->file_path)) {
                Storage::disk('public')->delete($product->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('products', $fileName, 'public');
            
            $data['file_path'] = $filePath;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        try {
            $product->update($data);
            return redirect()->route('admin.products')->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar produto: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteProduct($id)
    {
        $product = DigitalProduct::findOrFail($id);
        
        if ($product->file_path && Storage::disk('public')->exists($product->file_path)) {
            Storage::disk('public')->delete($product->file_path);
        }
        
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Produto excluído com sucesso!');
    }

    public function orders()
    {
        $orders = Order::with(['user', 'digitalProduct'])
            ->latest()
            ->paginate(20);
            
        return view('admin.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,cancelled,failed,refunded',
            'reason' => 'nullable|string|max:500'
        ]);

        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Atualizar status
        $order->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'approved' ? now() : null
        ]);

        // Se foi aprovado, criar compra do usuário
        if ($newStatus === 'approved' && !$order->user->hasPurchased($order->digital_product_id)) {
            $order->user->purchases()->create([
                'digital_product_id' => $order->digital_product_id,
                'purchased_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status do pedido alterado de ' . $oldStatus . ' para ' . $newStatus,
            'order' => $order->load(['user', 'digitalProduct'])
        ]);
    }

    public function deleteOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->delete();

        return redirect()->route('admin.orders')->with('success', 'Pedido excluído com sucesso!');
    }

    public function bulkUpdateOrders(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,approved,cancelled,failed,refunded',
            'reason' => 'nullable|string|max:500'
        ]);

        $orderIds = $request->order_ids;
        $newStatus = $request->status;
        $updatedCount = 0;

        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            
            if ($order) {
                $order->update([
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'approved' ? now() : null
                ]);

                // Se foi aprovado, criar compra do usuário
                if ($newStatus === 'approved' && !$order->user->hasPurchased($order->digital_product_id)) {
                    $order->user->purchases()->create([
                        'digital_product_id' => $order->digital_product_id,
                        'purchased_at' => now()
                    ]);
                }

                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $updatedCount . ' pedidos atualizados com sucesso!'
        ]);
    }

    public function users()
    {
        $users = User::withCount(['purchases', 'orders'])
            ->with(['purchases.digitalProduct'])
            ->latest()
            ->paginate(20);
            
        return view('admin.users.index', compact('users'));
    }

    public function settings()
    {
        $appearanceSettings = \App\Models\Setting::getAppearanceSettings();
        $brandingSettings = \App\Models\Setting::getBrandingSettings();
        $mercadopagoSettings = \App\Models\Setting::getMercadoPagoSettings();
        $generalSettings = \App\Models\Setting::getGeneralSettings();
        
        // Gera URL dinâmica do webhook baseada no APP_URL
        $webhookUrl = $this->generateWebhookUrl();
        $isDevelopment = $this->isDevelopmentEnvironment();
        
        return view('admin.settings.index', compact(
            'appearanceSettings', 
            'brandingSettings', 
            'mercadopagoSettings',
            'generalSettings',
            'webhookUrl',
            'isDevelopment'
        ));
    }

    /**
     * Gera a URL do webhook de forma dinâmica
     */
    private function generateWebhookUrl(): string
    {
        $baseUrl = config('app.url');
        
        // Se é ambiente de desenvolvimento local, mantém a URL como está
        if ($this->isDevelopmentEnvironment()) {
            return $baseUrl . '/api/webhooks/mercadopago';
        }
        
        // Para produção, força HTTPS se não estiver presente
        if (strpos($baseUrl, 'https://') !== 0 && strpos($baseUrl, 'http://') === 0) {
            $baseUrl = str_replace('http://', 'https://', $baseUrl);
        }
        
        return $baseUrl . '/api/webhooks/mercadopago';
    }

    /**
     * Verifica se está em ambiente de desenvolvimento
     */
    private function isDevelopmentEnvironment(): bool
    {
        $appUrl = config('app.url');
        $isDev = config('app.env') === 'local' || config('app.env') === 'development';
        $isLocalhost = strpos($appUrl, 'localhost') !== false || 
                      strpos($appUrl, '127.0.0.1') !== false || 
                      strpos($appUrl, '::1') !== false;
        
        return $isDev || $isLocalhost;
    }

    public function updateMercadoPagoSettings(Request $request)
    {
        $request->validate([
            'mercadopago_access_token' => 'nullable|string',
            'mercadopago_public_key' => 'nullable|string',
            'mercadopago_environment' => 'required|in:sandbox,production',
            'mercadopago_webhook_enabled' => 'nullable|in:0,1',
            'mercadopago_webhook_secret' => 'nullable|string',
        ]);

        \App\Models\Setting::set('mercadopago_access_token', $request->mercadopago_access_token, 'string', 'mercadopago');
        \App\Models\Setting::set('mercadopago_public_key', $request->mercadopago_public_key, 'string', 'mercadopago');
        \App\Models\Setting::set('mercadopago_environment', $request->mercadopago_environment, 'string', 'mercadopago');
        \App\Models\Setting::set('mercadopago_webhook_enabled', $request->mercadopago_webhook_enabled ?? '1', 'boolean', 'mercadopago');
        \App\Models\Setting::set('mercadopago_webhook_secret', $request->mercadopago_webhook_secret, 'string', 'mercadopago');

        return redirect()->route('admin.settings')->with('success', 'Configurações do Mercado Pago atualizadas!');
    }

    public function updateGeneralSettings(Request $request)
    {
        $request->validate([
            'homepage_type' => 'required|in:login,custom',
            'homepage_url' => 'nullable|url|max:255',
            'homepage_enabled' => 'nullable|in:0,1',
        ]);

        // Se o tipo é 'custom' mas não tem URL, voltar com erro
        if ($request->homepage_type === 'custom' && empty($request->homepage_url)) {
            return redirect()->back()
                ->withErrors(['homepage_url' => 'A URL é obrigatória quando o tipo for "Página Personalizada".'])
                ->withInput();
        }

        \App\Models\Setting::set('homepage_type', $request->homepage_type, 'string', 'general');
        \App\Models\Setting::set('homepage_url', $request->homepage_url, 'string', 'general');
        \App\Models\Setting::set('homepage_enabled', $request->homepage_enabled ?? '0', 'boolean', 'general');

        return redirect()->route('admin.settings')->with('success', 'Configurações gerais atualizadas!');
    }

    public function updateAppearanceSettings(Request $request)
    {
        $request->validate([
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'background_color' => 'required|string',
            'card_background' => 'required|string',
        ]);

        \App\Models\Setting::set('primary_color', $request->primary_color, 'color', 'appearance');
        \App\Models\Setting::set('secondary_color', $request->secondary_color, 'color', 'appearance');
        \App\Models\Setting::set('background_color', $request->background_color, 'color', 'appearance');
        \App\Models\Setting::set('card_background', $request->card_background, 'color', 'appearance');

        return redirect()->route('admin.settings')->with('success', 'Configurações de aparência atualizadas!');
    }

    public function updateBrandingSettings(Request $request)
    {
        try {
            // Log de debug para início da função
            $debugInfo = [
                'has_logo' => $request->hasFile('logo'),
                'has_favicon' => $request->hasFile('favicon'),
                'files' => $request->allFiles()
            ];
            
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $debugInfo['logo_details'] = [
                    'original_name' => $logoFile->getClientOriginalName(),
                    'size' => $logoFile->getSize(),
                    'mime_type' => $logoFile->getMimeType(),
                    'client_mime_type' => $logoFile->getClientMimeType(),
                    'extension' => $logoFile->getClientOriginalExtension(),
                    'is_valid' => $logoFile->isValid(),
                    'error' => $logoFile->getError(),
                ];
            }
            
            \Illuminate\Support\Facades\Log::info('updateBrandingSettings iniciado', $debugInfo);

            $request->validate([
                'site_name' => 'required|string|max:255',
                'site_description' => 'nullable|string|max:500',
                'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,gif,webp|max:1024',
            ]);

            \App\Models\Setting::set('site_name', $request->site_name, 'string', 'branding');
            \App\Models\Setting::set('site_description', $request->site_description, 'string', 'branding');

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                
                \Illuminate\Support\Facades\Log::info('Processando upload de logo', [
                    'original_name' => $logoFile->getClientOriginalName(),
                    'size' => $logoFile->getSize(),
                    'mime_type' => $logoFile->getMimeType(),
                    'is_valid' => $logoFile->isValid()
                ]);

                if (!$logoFile->isValid()) {
                    throw new \Exception('Arquivo de logo inválido');
                }
                
                // Remove logo antigo se existir
                $oldLogoPath = \App\Models\Setting::get('logo_path');
                if ($oldLogoPath && file_exists(public_path($oldLogoPath))) {
                    unlink(public_path($oldLogoPath));
                    \Illuminate\Support\Facades\Log::info('Logo antigo removido', ['path' => $oldLogoPath]);
                }
                
                // Verificar se o diretório existe
                $brandingDir = public_path('uploads/branding');
                if (!file_exists($brandingDir)) {
                    mkdir($brandingDir, 0755, true);
                    \Illuminate\Support\Facades\Log::info('Diretório branding criado', ['path' => $brandingDir]);
                }
                
                // Salvar arquivo diretamente na pasta public
                $fileName = time() . '_' . $logoFile->getClientOriginalName();
                $logoFile->move($brandingDir, $fileName);
                $logoPath = 'uploads/branding/' . $fileName;
                \App\Models\Setting::set('logo_path', $logoPath, 'string', 'branding');
                
                \Illuminate\Support\Facades\Log::info('Logo salvo com sucesso', [
                    'path' => $logoPath,
                    'full_path' => storage_path('app/public/' . $logoPath)
                ]);
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                $faviconFile = $request->file('favicon');
                
                \Illuminate\Support\Facades\Log::info('Processando upload de favicon', [
                    'original_name' => $faviconFile->getClientOriginalName(),
                    'size' => $faviconFile->getSize(),
                    'mime_type' => $faviconFile->getMimeType(),
                    'is_valid' => $faviconFile->isValid()
                ]);

                if (!$faviconFile->isValid()) {
                    throw new \Exception('Arquivo de favicon inválido');
                }
                
                // Remove favicon antigo se existir
                $oldFaviconPath = \App\Models\Setting::get('favicon_path');
                if ($oldFaviconPath && file_exists(public_path($oldFaviconPath))) {
                    unlink(public_path($oldFaviconPath));
                    \Illuminate\Support\Facades\Log::info('Favicon antigo removido', ['path' => $oldFaviconPath]);
                }
                
                // Verificar se o diretório existe
                $brandingDir = public_path('uploads/branding');
                if (!file_exists($brandingDir)) {
                    mkdir($brandingDir, 0755, true);
                    \Illuminate\Support\Facades\Log::info('Diretório branding criado para favicon', ['path' => $brandingDir]);
                }
                
                // Salvar arquivo diretamente na pasta public
                $fileName = time() . '_favicon_' . $faviconFile->getClientOriginalName();
                $faviconFile->move($brandingDir, $fileName);
                $faviconPath = 'uploads/branding/' . $fileName;
                \App\Models\Setting::set('favicon_path', $faviconPath, 'string', 'branding');
                
                \Illuminate\Support\Facades\Log::info('Favicon salvo com sucesso', [
                    'path' => $faviconPath,
                    'full_path' => storage_path('app/public/' . $faviconPath)
                ]);
            }

            return redirect()->route('admin.settings')->with('success', 'Configurações de marca atualizadas!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Erro de validação no upload', [
                'errors' => $e->errors()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro no upload de branding', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Erro ao fazer upload: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleAdmin($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->hasRole('admin')) {
            $user->removeRole('admin');
            $user->assignRole('member');
            $message = 'Usuário removido como administrador!';
        } else {
            $user->assignRole('admin');
            $user->removeRole('member');
            $message = 'Usuário promovido a administrador!';
        }
        
        return redirect()->route('admin.users')->with('success', $message);
    }

    public function removeLogo()
    {
        try {
            $logoPath = \App\Models\Setting::get('logo_path');
            
            if ($logoPath && file_exists(public_path($logoPath))) {
                unlink(public_path($logoPath));
            }
            
            \App\Models\Setting::set('logo_path', null, 'string', 'branding');
            
            return response()->json(['success' => true, 'message' => 'Logo removido com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao remover logo: ' . $e->getMessage()]);
        }
    }

    public function removeFavicon()
    {
        try {
            $faviconPath = \App\Models\Setting::get('favicon_path');
            
            if ($faviconPath && file_exists(public_path($faviconPath))) {
                unlink(public_path($faviconPath));
            }
            
            \App\Models\Setting::set('favicon_path', null, 'string', 'branding');
            
            return response()->json(['success' => true, 'message' => 'Favicon removido com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao remover favicon: ' . $e->getMessage()]);
        }
    }

    // ===== GERENCIAMENTO DE CURSOS =====
    
    public function courses()
    {
        $courses = DigitalProduct::withCount(['activeModules'])->orderBy('order')->get();
        
        // Calcular total de aulas para cada curso
        foreach ($courses as $course) {
            $course->total_lessons = $course->getTotalLessonsCount();
        }
        
        return view('admin.courses.index', compact('courses'));
    }

    public function courseModules($id)
    {
        $course = DigitalProduct::findOrFail($id);
        $modules = $course->activeModules()->withCount('activeLessons')->get();
        
        return view('admin.courses.modules', compact('course', 'modules'));
    }

    public function createModule($courseId)
    {
        $course = DigitalProduct::findOrFail($courseId);
        return view('admin.courses.modules.create', compact('course'));
    }

    public function storeModule(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $course = DigitalProduct::findOrFail($courseId);
        
        \App\Models\Module::create([
            'digital_product_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.courses.modules', $course->id)
            ->with('success', 'Módulo criado com sucesso!');
    }

    public function editModule($id)
    {
        $module = \App\Models\Module::with('digitalProduct')->findOrFail($id);
        return view('admin.courses.modules.edit', compact('module'));
    }

    public function updateModule(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $module = \App\Models\Module::findOrFail($id);
        
        $module->update([
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.courses.modules', $module->digitalProduct->id)
            ->with('success', 'Módulo atualizado com sucesso!');
    }

    public function deleteModule($id)
    {
        $module = \App\Models\Module::findOrFail($id);
        $courseId = $module->digital_product_id;
        
        $module->delete();

        return redirect()->route('admin.courses.modules', $courseId)
            ->with('success', 'Módulo excluído com sucesso!');
    }

    // ===== GERENCIAMENTO DE AULAS =====
    
    public function moduleLessons($id)
    {
        $module = \App\Models\Module::with(['digitalProduct', 'activeLessons'])->findOrFail($id);
        return view('admin.courses.lessons.index', compact('module'));
    }

    public function createLesson($moduleId)
    {
        $module = \App\Models\Module::with('digitalProduct')->findOrFail($moduleId);
        return view('admin.courses.lessons.create', compact('module'));
    }

    public function storeLesson(Request $request, $moduleId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:text,video,iframe,file',
            'content_url' => 'nullable|url',
            'content_text' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:0',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_free' => 'boolean',
        ]);

        $module = \App\Models\Module::findOrFail($moduleId);
        
        \App\Models\Lesson::create([
            'module_id' => $module->id,
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $request->content_type,
            'content_url' => $request->content_url,
            'content_text' => $request->content_text,
            'duration_minutes' => $request->duration_minutes,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
            'is_free' => $request->has('is_free'),
        ]);

        return redirect()->route('admin.modules.lessons', $module->id)
            ->with('success', 'Aula criada com sucesso!');
    }

    public function editLesson($id)
    {
        $lesson = \App\Models\Lesson::with(['module.digitalProduct'])->findOrFail($id);
        return view('admin.courses.lessons.edit', compact('lesson'));
    }

    public function updateLesson(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:text,video,iframe,file',
            'content_url' => 'nullable|url',
            'content_text' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:0',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_free' => 'boolean',
        ]);

        $lesson = \App\Models\Lesson::findOrFail($id);
        
        $lesson->update([
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $request->content_type,
            'content_url' => $request->content_url,
            'content_text' => $request->content_text,
            'duration_minutes' => $request->duration_minutes,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
            'is_free' => $request->has('is_free'),
        ]);

        return redirect()->route('admin.modules.lessons', $lesson->module_id)
            ->with('success', 'Aula atualizada com sucesso!');
    }

    public function deleteLesson($id)
    {
        $lesson = \App\Models\Lesson::findOrFail($id);
        $moduleId = $lesson->module_id;
        
        $lesson->delete();

        return redirect()->route('admin.modules.lessons', $moduleId)
            ->with('success', 'Aula excluída com sucesso!');
    }

    // Métodos para Banners
    public function banners()
    {
        $banners = Banner::ordered()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function createBanner()
    {
        return view('admin.banners.create');
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $imagePath,
            'link' => $request->link,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.banners')
            ->with('success', 'Banner criado com sucesso!');
    }

    public function editBanner($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ];

        if ($request->hasFile('image')) {
            // Deletar imagem antiga
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners')
            ->with('success', 'Banner atualizado com sucesso!');
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        
        // Remove a imagem se existir
        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }
        
        $banner->delete();
        
        return redirect()->route('admin.banners')->with('success', 'Banner excluído com sucesso!');
    }

    public function onlineUsers()
    {
        // Buscar usuários que estiveram ativos nos últimos 5 minutos
        $onlineUsers = User::where('last_activity', '>=', now()->subMinutes(5))
            ->with(['roles', 'permissions'])
            ->get()
            ->map(function ($user) {
                // Mapear páginas para nomes amigáveis
                $pages = [
                    'membership.index' => 'Dashboard',
                    'membership.course' => 'Cursos',
                    'membership.lesson' => 'Aulas',
                    'membership.profile' => 'Perfil',
                    'admin.dashboard' => 'Painel Admin',
                    'admin.products' => 'Produtos',
                    'admin.orders' => 'Pedidos',
                    'admin.users' => 'Usuários',
                    'admin.settings' => 'Configurações',
                    'admin.online-users' => 'Usuários Online'
                ];
                
                $currentPage = $pages[$user->current_page] ?? 'Navegando';
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => strtoupper(substr($user->name, 0, 1)),
                    'last_activity' => $user->last_activity,
                    'current_page' => $currentPage,
                    'is_admin' => $user->isAdmin(),
                    'online_duration' => $user->last_activity ? $user->last_activity->diffForHumans() : 'Desconhecido',
                    'status' => $user->last_activity && $user->last_activity->diffInMinutes(now()) <= 1 ? 'online' : 'away'
                ];
            });

        return view('admin.online-users', compact('onlineUsers'));
    }

    // ===== FERRAMENTAS DO SISTEMA =====
    
    public function clearCache()
    {
        try {
            // Limpar diferentes tipos de cache
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso! (Config, Routes, Views e Application Cache)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadBackup()
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupName = "backup_sistema_{$timestamp}.sql";
            $backupPath = storage_path("app/backups/{$backupName}");
            
            // Criar diretório de backup se não existir
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            $dbConfig = config('database.connections.' . config('database.default'));
            
            // Verificar se mysqldump está disponível
            $mysqldumpPath = $this->findMysqldump();
            
            if (!$mysqldumpPath) {
                throw new \Exception('mysqldump não encontrado no sistema. Instale MySQL Client Tools.');
            }
            
            $command = sprintf(
                '%s --single-transaction --routines --triggers -h%s -u%s -p%s %s > %s 2>/dev/null',
                $mysqldumpPath,
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($backupPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupPath) && filesize($backupPath) > 0) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                // Se falhou, tentar método alternativo usando Laravel
                return $this->createBackupAlternative($backupName, $backupPath);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    private function findMysqldump()
    {
        $paths = [
            'mysqldump', // PATH do sistema
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump', // macOS Homebrew
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe', // Windows
        ];

        foreach ($paths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // Tentar encontrar via which/where
        $which = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        exec("$which mysqldump 2>/dev/null", $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    private function createBackupAlternative($backupName, $backupPath)
    {
        // Método alternativo usando consultas Laravel (mais lento, mas funciona)
        $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES");
        $sql = "-- Backup gerado pelo sistema em " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            
            // Estrutura da tabela
            $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "-- Estrutura da tabela `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Dados da tabela
            $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dados da tabela `{$tableName}`\n";
                $sql .= "INSERT INTO `{$tableName}` VALUES \n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowData = array_map(function($value) {
                        return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    $values[] = '(' . implode(', ', $rowData) . ')';
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        file_put_contents($backupPath, $sql);
        
        if (file_exists($backupPath) && filesize($backupPath) > 0) {
            return response()->download($backupPath)->deleteFileAfterSend(true);
        } else {
            throw new \Exception('Falha ao criar backup alternativo');
        }
    }

    public function getSystemLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            
            if (!file_exists($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo de log não encontrado'
                ], 404);
            }
            
            // Ler as últimas 100 linhas do log
            $lines = [];
            $file = file($logPath);
            $totalLines = count($file);
            $startLine = max(0, $totalLines - 100);
            
            for ($i = $startLine; $i < $totalLines; $i++) {
                $lines[] = $file[$i];
            }
            
            $logContent = implode('', array_reverse($lines)); // Mais recentes primeiro
            
            return response()->json([
                'success' => true,
                'logs' => $logContent,
                'total_lines' => $totalLines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ler logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
