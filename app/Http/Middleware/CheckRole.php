<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        if ($role === 'restaurateur' && !$user->isRestaurateur()) {
            abort(403, 'Accès réservé aux restaurateurs.');
        }

        if ($role === 'client' && !$user->isClient()) {
            abort(403, 'Accès réservé aux clients.');
        }

        if ($role === 'admin' && !$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
