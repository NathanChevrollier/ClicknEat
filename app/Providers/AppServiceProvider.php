<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Partage les classes couramment utilisées avec toutes les vues
        view()->share('Auth', Auth::class);
        view()->share('Route', Route::class);
        
        // Directive Blade personnalisée pour formater les prix avec le symbole euro
        Blade::directive('formatPrice', function ($expression) {
            return "<?php echo number_format($expression / 100, 2, ',', ' ') . ' &euro;'; ?>";
        });
        
        // Helper global pour formater les prix
        if (!function_exists('format_price')) {
            function format_price($price) {
                return number_format($price / 100, 2, ',', ' ') . ' €';
            }
        }
        
        // Helper global pour corriger l'encodage des caractères spéciaux
        if (!function_exists('fix_encoding')) {
            function fix_encoding($text) {
                if (is_null($text)) {
                    return null;
                }
                
                // Détecte l'encodage actuel et convertit en UTF-8 si nécessaire
                $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                if ($encoding != 'UTF-8') {
                    $text = mb_convert_encoding($text, 'UTF-8', $encoding);
                }
                
                // Remplace les séquences problématiques comme é -> é
                $text = preg_replace_callback('/u([0-9a-f]{4})/i', function($matches) {
                    return html_entity_decode('&#x' . $matches[1] . ';', ENT_QUOTES, 'UTF-8');
                }, $text);
                
                return $text;
            }
        }
        
        // Modifier automatiquement les valeurs affichées dans les vues
        view()->composer('*', function ($view) {
            $data = $view->getData();
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $view[$key] = fix_encoding($value);
                }
            }
        });
    }
}
