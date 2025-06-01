<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\View;

class ShareCategoriesWithViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Partager la variable $categories avec toutes les vues
        if (!View::shared('categories')) {
            $categories = Category::all();
            View::share('categories', $categories);
        }
        
        return $next($request);
    }
}
