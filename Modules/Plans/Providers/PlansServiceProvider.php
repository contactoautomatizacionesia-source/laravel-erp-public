<?php

namespace Modules\Plans\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Plans\Entities\Rule;
use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Rules\RuleCheckerRegistry;
use Modules\Plans\Pipeline\Rules\Checkers\PointsThresholdChecker;
use Modules\Plans\Pipeline\Rules\Checkers\PointsRangeChecker;
use Modules\Plans\Pipeline\Rules\Checkers\CycleCompletionChecker;
use Modules\Plans\Pipeline\Rules\Checkers\PointsPerCycleChecker;
use Modules\Plans\Pipeline\Rules\Checkers\RuleGroupingChecker;
use Modules\Plans\Pipeline\Rules\Checkers\DownlineTitleCountChecker;
use Modules\Plans\Pipeline\Rules\Checkers\LifeTitleCountChecker;
use Modules\Plans\Pipeline\Rules\Checkers\DocumentationFormalizationChecker;
use Modules\Plans\Pipeline\Benefits\BenefitCheckerRegistry;
use Modules\Plans\Pipeline\Benefits\Checkers\DiscountOnNextPurchaseChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\ReferredPurchaseDifferentialChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\AccumulatePointsLevelUpChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\FirstReferredPurchaseBenefitChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\NewPlatformPermissionChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\MaterialRewardOrRecognitionChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\MonetaryBonusChecker;
use Modules\Plans\Pipeline\Benefits\Checkers\NetworkBenefitsChecker;

class PlansServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Plans';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'plans';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        Relation::morphMap([
            'rule'    => Rule::class,
            'benefit' => Benefit::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(RuleCheckerRegistry::class, function ($app) {
            return new RuleCheckerRegistry([
                $app->make(PointsThresholdChecker::class),
                $app->make(PointsRangeChecker::class),
                $app->make(CycleCompletionChecker::class),
                $app->make(PointsPerCycleChecker::class),
                $app->make(RuleGroupingChecker::class),
                $app->make(DownlineTitleCountChecker::class),
                $app->make(LifeTitleCountChecker::class),
                $app->make(DocumentationFormalizationChecker::class),
            ]);
        });

        $this->app->singleton(BenefitCheckerRegistry::class, function ($app) {
            return new BenefitCheckerRegistry([
                $app->make(DiscountOnNextPurchaseChecker::class),
                $app->make(ReferredPurchaseDifferentialChecker::class),
                $app->make(AccumulatePointsLevelUpChecker::class),
                $app->make(FirstReferredPurchaseBenefitChecker::class),
                $app->make(NewPlatformPermissionChecker::class),
                $app->make(MaterialRewardOrRecognitionChecker::class),
                $app->make(MonetaryBonusChecker::class),
                $app->make(NetworkBenefitsChecker::class),
            ]);
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
