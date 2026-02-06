<?php

namespace Crumbls\HelpDesk;

use Crumbls\HelpDesk\Contracts\SlaCalculator;
use Crumbls\HelpDesk\Services\SlaService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HelpDeskServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'helpdesk');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'helpdesk');

        $this->publishes([
            __DIR__ . '/../config/helpdesk.php' => config_path('helpdesk.php'),
        ], 'helpdesk-config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/helpdesk'),
        ], 'helpdesk-translations');

        $this->publishes([
            __DIR__ . '/Database/Migrations/' => database_path('migrations'),
        ], 'helpdesk-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/helpdesk'),
        ], 'helpdesk-views');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->registerPolicies();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/helpdesk.php',
            'helpdesk'
        );

        $this->registerSlaService();
    }

    /**
     * Register policies for helpdesk models.
     */
    protected function registerPolicies(): void
    {
        $resources = ['ticket', 'comment', 'department', 'priority', 'status', 'type'];

        foreach ($resources as $resource) {
            $policyClass = config("helpdesk.api.{$resource}.policy");

            if ($policyClass && class_exists($policyClass)) {
                $modelClass = Models::$resource();
                Gate::policy($modelClass, $policyClass);
            }
        }
    }

    /**
     * Register the SLA service.
     */
    protected function registerSlaService(): void
    {
        $this->app->bind(SlaCalculator::class, function ($app) {
            $serviceClass = config('helpdesk.sla.calculator', SlaService::class);

            return new $serviceClass();
        });

        $this->app->alias(SlaCalculator::class, 'helpdesk.sla');
    }
}
