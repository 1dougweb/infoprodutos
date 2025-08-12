<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Verificar se o usuário tem role admin ou é admin pelo campo is_admin
        if (!Auth::user()->hasRole('admin') && !Auth::user()->is_admin) {
            \Log::error('Usuário não tem role admin: ' . Auth::user()->email);
            \Log::error('Roles do usuário: ' . Auth::user()->getRoleNames()->implode(', '));
            \Log::error('is_admin: ' . (Auth::user()->is_admin ? 'true' : 'false'));
            return redirect()->route('membership.index')->with('error', 'Acesso negado. Você precisa ser administrador.');
        }

        return $next($request);
    }
}
