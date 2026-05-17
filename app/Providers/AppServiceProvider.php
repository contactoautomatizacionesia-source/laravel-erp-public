<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Laravel\Scout\Console\ImportCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Modules\Plans\Helpers\PlanContextHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(TranslationServiceProvider::class);

        $this->app->singleton('general_setting', function () {
            return DB::table('general_settings')->first();
        });

        // Permite controlar la validación de licencia mediante LICENSE_CHECK_ENABLED en .env
        $this->app->bind(
            \SpondonIt\Service\Repositories\InitRepository::class,
            \App\Overrides\LicenseCheckRepository::class
        );

        // Omite el middleware de verificacion UXSeven cuando LICENSE_CHECK_ENABLED=false
        $this->app->bind(
            \SpondonIt\Service\Middleware\ServiceMiddleware::class,
            \App\Overrides\LicenseServiceMiddleware::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Configuración dinámica de Zona Horaria
        try {
            if (!$this->app->runningInConsole()) {
                $generalSetting = app('general_setting');
                if ($generalSetting && isset($generalSetting->time_zone)) {
                    $timezone = $generalSetting->time_zone;
                    
                    // Sincroniza PHP y Laravel con la zona horaria de la DB
                    date_default_timezone_set($timezone);
                    Config::set('app.timezone', $timezone);
                }
            }
        } catch (\Exception $e) {
            // Evita que la app falle si la tabla de settings no existe aún
            date_default_timezone_set('America/Bogota');
        }

        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate',
                function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))
                    ->withPath('');
            });
        }

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }


        Schema::defaultStringLength(191);


        Validator::extend('check_unique_phone', function($attribute, $value, $parameters, $validator) {
            if (is_numeric($value)) {
              $data=User::where('phone',$value)->first();
              if($data){
                return false;
               }
                return true;
            }
            return true;

        });

        Paginator::useBootstrap();

        View::composer(
            'frontend.amazy.pages.profile.partials._customer_details',
            function ($view) {

                static $planContext;

                if (!$planContext && Auth::check()) {
                    $planContext = PlanContextHelper::resolve(
                        userId: Auth::user()->id
                    );
                }

                $view->with('planContext', $planContext);
            }
        );

    }
}
