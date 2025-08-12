<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('DebugAuthMiddleware - URL: ' . $request->url());
        Log::info('DebugAuthMiddleware - Session ID: ' . session()->getId());
        Log::info('DebugAuthMiddleware - Auth Check: ' . (Auth::check() ? 'true' : 'false'));
        
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('DebugAuthMiddleware - User ID: ' . $user->id);
            Log::info('DebugAuthMiddleware - User Email: ' . $user->email);
        } else {
            Log::info('DebugAuthMiddleware - No user logged in');
        }
        
        return $next($request);
    }
} 