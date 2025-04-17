<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurateurMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        if (!auth()->user()->isRestaurateur()) {
            // Rediriger vers le tableau de bord approprié plutôt que de bloquer l'accès
            if (auth()->user()->isClient()) {
                return redirect()->route('client.dashboard')
                    ->with('warning', 'Cette section est réservée aux restaurateurs.');
            } elseif (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Cette section est réservée aux restaurateurs.');
            }
        }

        return $next($request);
    }
}
