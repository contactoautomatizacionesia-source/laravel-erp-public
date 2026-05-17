<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Translatable;

class TranslatableConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            $translatable = app(Translatable::class);
            $translatable->fallbackLocale = 'en';
            $translatable->fallbackAny = true;             
        } catch (\Exception $e) {
            // Silenciar errores si el paquete no está presente
        }
    }
}
