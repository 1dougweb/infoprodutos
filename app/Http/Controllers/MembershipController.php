<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\DigitalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Construir query com filtros
        $query = DigitalProduct::active()->ordered();
        
        // Aplicar filtros se fornecidos
        if ($request->filled('section')) {
            $query->bySection($request->section);
        }
        
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        
        if ($request->filled('access_type')) {
            if ($request->access_type === 'free') {
                $query->free();
            } elseif ($request->access_type === 'paid') {
                $query->paid();
            }
        }
        
        // Filtro por status de acesso do usuário
        if ($request->filled('user_access')) {
            if ($request->user_access === 'purchased') {
                $query->whereHas('purchases', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } elseif ($request->user_access === 'not_purchased') {
                $query->whereDoesntHave('purchases', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }
        
        $products = $query->get();
        $banners = Banner::active()->ordered()->get();
        
        // Obter dados para filtros
        $sections = DigitalProduct::active()->distinct()->pluck('section');
        $categories = DigitalProduct::active()->distinct()->pluck('category');
        
        // Verificar se veio de um pagamento bem-sucedido
        if ($request->query('payment_success') === 'true') {
            $orderId = $request->query('order_id');
            
            // Buscar o pedido para mostrar informações
            $order = \App\Models\Order::find($orderId);
            if ($order && $order->status === 'approved') {
                return view('membership.index', compact('user', 'products', 'banners', 'sections', 'categories'))
                    ->with('success', 'Pagamento aprovado! Produto liberado para acesso.');
            }
        }
        
        return view('membership.index', compact('user', 'products', 'banners', 'sections', 'categories'));
    }

    public function download($id)
    {
        $user = Auth::user();
        $product = DigitalProduct::findOrFail($id);
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($product->id)) {
            return redirect()->back()->with('error', 'Você precisa comprar este produto para baixá-lo.');
        }
        
        // Verificar se o arquivo existe
        if (!$product->file_path || !Storage::exists($product->file_path)) {
            return redirect()->back()->with('error', 'Arquivo não encontrado.');
        }
        
        return Storage::download($product->file_path, $product->file_name);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('membership.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return redirect()->route('membership.profile')->with('success', 'Perfil atualizado com sucesso!');
    }
    
    public function course($id)
    {
        $user = Auth::user();
        $product = DigitalProduct::with(['activeModules.activeLessons'])->findOrFail($id);
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($product->id)) {
            return redirect()->route('membership.index')->with('error', 'Você precisa comprar este curso para acessá-lo.');
        }
        
        $modules = $product->activeModules;
        $firstLesson = $modules->first()?->activeLessons->first();
        
        // Se não há aulas, mostrar página vazia
        if (!$firstLesson) {
            return view('membership.course', compact('user', 'product', 'modules', 'firstLesson'));
        }
        
        // Redirecionar para a primeira aula automaticamente
        return redirect()->route('membership.lesson', [$product->id, $firstLesson->id]);
    }

    public function lesson($productId, $lessonId)
    {
        $user = Auth::user();
        $product = DigitalProduct::with(['activeModules.activeLessons'])->findOrFail($productId);
        $lesson = \App\Models\Lesson::with(['module'])->findOrFail($lessonId);
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($product->id)) {
            return redirect()->route('membership.index')->with('error', 'Você precisa comprar este curso para acessá-lo.');
        }
        
        // Verificar se a aula pertence ao curso
        if ($lesson->module->digital_product_id != $product->id) {
            return redirect()->route('membership.course', $product->id)->with('error', 'Aula não encontrada neste curso.');
        }
        
        $modules = $product->activeModules;
        $currentModule = $lesson->module;
        $comments = $lesson->comments;
        
        // Buscar progresso do usuário para esta aula
        $userProgress = \App\Models\UserProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();
        
        return view('membership.lesson', compact('user', 'product', 'lesson', 'modules', 'currentModule', 'comments', 'userProgress'));
    }

    public function updateProgress(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'watched_seconds' => 'nullable|integer|min:0',
            'is_completed' => 'nullable|boolean',
        ]);
        
        $lesson = \App\Models\Lesson::findOrFail($request->lesson_id);
        
        // Verificar se o usuário tem acesso ao curso
        if (!$user->hasPurchased($lesson->module->digital_product_id)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $progress = \App\Models\UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'watched_seconds' => $request->watched_seconds ?? 0,
                'is_completed' => $request->is_completed ?? false,
            ]
        );
        
        if ($request->is_completed) {
            $progress->markAsCompleted();
        }
        
        return response()->json(['success' => true, 'progress' => $progress]);
    }

    public function downloadLesson($lessonId)
    {
        $user = Auth::user();
        $lesson = \App\Models\Lesson::with(['module.digitalProduct'])->findOrFail($lessonId);
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($lesson->module->digital_product_id)) {
            return redirect()->back()->with('error', 'Você precisa comprar este curso para baixar os arquivos.');
        }
        
        // Verificar se é um arquivo
        if ($lesson->content_type !== 'file') {
            return redirect()->back()->with('error', 'Esta aula não possui arquivo para download.');
        }
        
        // Verificar se o arquivo existe
        if (!$lesson->content_url || !Storage::exists($lesson->content_url)) {
            return redirect()->back()->with('error', 'Arquivo não encontrado.');
        }
        
        // Marcar como concluída automaticamente
        $progress = \App\Models\UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => true,
            ]
        );
        
        return Storage::download($lesson->content_url);
    }

    public function storeComment(Request $request, $lessonId)
    {
        $user = Auth::user();
        $lesson = \App\Models\Lesson::findOrFail($lessonId);
        
        // Verificar se o usuário tem acesso ao curso
        if (!$user->hasPurchased($lesson->module->digital_product_id)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);
        
        $comment = \App\Models\Comment::create([
            'user_id' => $user->id,
            'lesson_id' => $lessonId,
            'parent_id' => $request->parent_id,
            'content' => $request->content
        ]);
        
        $comment->load('user');
        
        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comentário adicionado com sucesso!'
        ]);
    }

    public function updateComment(Request $request, $commentId)
    {
        $user = Auth::user();
        $comment = \App\Models\Comment::findOrFail($commentId);
        
        if (!$comment->canBeEditedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);
        
        $comment->update([
            'content' => $request->content
        ]);
        
        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comentário atualizado com sucesso!'
        ]);
    }

    public function deleteComment($commentId)
    {
        $user = Auth::user();
        $comment = \App\Models\Comment::findOrFail($commentId);
        
        if (!$comment->canBeDeletedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }
        
        $comment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Comentário excluído com sucesso!'
        ]);
    }

    public function digitalProduct($id)
    {
        $user = Auth::user();
        $product = DigitalProduct::findOrFail($id);
        
        // Verificar se é um produto digital
        if ($product->product_type !== 'digital') {
            return redirect()->route('membership.course', $id);
        }
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($product->id)) {
            return redirect()->route('membership.index')->with('error', 'Você precisa comprar este produto para acessá-lo.');
        }
        
        return view('membership.digital-product', compact('user', 'product'));
    }

    public function downloadDigitalProduct($id)
    {
        $user = Auth::user();
        $product = DigitalProduct::findOrFail($id);
        
        // Verificar se é um produto digital
        if ($product->product_type !== 'digital') {
            return redirect()->back()->with('error', 'Este produto não é um produto digital.');
        }
        
        // Verificar se o usuário comprou o produto
        if (!$user->hasPurchased($product->id)) {
            return redirect()->back()->with('error', 'Você precisa comprar este produto para baixá-lo.');
        }
        
        // Verificar se o arquivo existe
        if (!$product->file_path || !Storage::disk('public')->exists($product->file_path)) {
            return redirect()->back()->with('error', 'Arquivo não encontrado.');
        }
        
        return Storage::disk('public')->download($product->file_path, $product->file_name);
    }
}
