<?php

namespace Crumbls\HelpDesk;

use Crumbls\HelpDesk\Console\Commands\AutoCloseStaleTickets;
use Crumbls\HelpDesk\Console\Commands\ProcessInboundEmail;
use Crumbls\HelpDesk\Contracts\SlaCalculator;
use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Events\SatisfactionRated;
use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Listeners\SendCommentCreatedNotifications;
use Crumbls\HelpDesk\Listeners\LogTicketActivity;
use Crumbls\HelpDesk\Listeners\SendTicketAssignedNotification;
use Crumbls\HelpDesk\Listeners\DispatchWebhook;
use Crumbls\HelpDesk\Listeners\SendTicketCreatedNotifications;
use Crumbls\HelpDesk\Listeners\SendTicketStatusChangedNotifications;
use Crumbls\HelpDesk\Services\SlaService;
use Illuminate\Support\Facades\Event;
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

        $this->registerEventListeners();

        $this->registerCommands();
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
     * Register event listeners for notifications.
     */
    protected function registerEventListeners(): void
    {
        if (!config('helpdesk.notifications.enabled', true)) {
            return;
        }

        Event::listen(TicketCreated::class, SendTicketCreatedNotifications::class);
        Event::listen(CommentCreated::class, SendCommentCreatedNotifications::class);
        Event::listen(TicketStatusChanged::class, SendTicketStatusChangedNotifications::class);
        Event::listen(TicketAssigned::class, SendTicketAssignedNotification::class);

        // Activity log.
        if (config('helpdesk.activity_log.enabled', true)) {
            Event::listen(TicketCreated::class, LogTicketActivity::class);
            Event::listen(CommentCreated::class, LogTicketActivity::class);
            Event::listen(TicketStatusChanged::class, LogTicketActivity::class);
            Event::listen(TicketAssigned::class, LogTicketActivity::class);
            Event::listen(SatisfactionRated::class, LogTicketActivity::class);
        }

        // Webhooks.
        if (config('helpdesk.webhooks')) {
            Event::listen(TicketCreated::class, DispatchWebhook::class);
            Event::listen(CommentCreated::class, DispatchWebhook::class);
            Event::listen(TicketStatusChanged::class, DispatchWebhook::class);
            Event::listen(TicketAssigned::class, DispatchWebhook::class);
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

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoCloseStaleTickets::class,
                ProcessInboundEmail::class,
            ]);
        }
    }
}
