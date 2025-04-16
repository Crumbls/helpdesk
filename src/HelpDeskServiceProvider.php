<?php

namespace Crumbls\HelpDesk;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class HelpDeskServiceProvider extends PanelProvider
{
    public function configurePackage(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'helpdesk');
        
        $this->publishes([
            __DIR__ . '/Config/helpdesk.php' => config_path('helpdesk.php'),
        ], 'helpdesk-config');
        
        $this->publishes([
            __DIR__ . '/Database/Migrations/' => database_path('migrations'),
        ], 'helpdesk-migrations');
        
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/helpdesk'),
        ], 'helpdesk-views');
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('helpdesk')
            ->path(config('helpdesk.path', 'helpdesk'))
            ->login()
            ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'Crumbls\\HelpDesk\\Filament\\Resources')
            ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'Crumbls\\HelpDesk\\Filament\\Pages')
            ->pages([
                \Crumbls\HelpDesk\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'Crumbls\\HelpDesk\\Filament\\Widgets')
            ->navigationGroups([
                'Tickets',
                'Categories',
                'Settings',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        $this->configurePackage();
    }

    public function register(): void
    {
        parent::register();
        
        $this->mergeConfigFrom(
            __DIR__ . '/Config/helpdesk.php',
            'helpdesk'
        );
    }
}