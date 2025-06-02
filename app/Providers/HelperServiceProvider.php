<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\AppHelper;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Enregistrer une directive Blade pour formater les prix
        Blade::directive('formatprice', function ($expression) {
            // Diviser l'expression pour gérer le second paramètre optionnel
            $params = explode(',', $expression);
            
            if (count($params) > 1) {
                return "<?php echo \App\Helpers\AppHelper::formatPrice($params[0], $params[1]); ?>";
            }
            
            return "<?php echo \App\Helpers\AppHelper::formatPrice($params[0]); ?>";
        });
    }
}
