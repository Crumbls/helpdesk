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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;


class HelpDeskServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
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
	/**
	 * Config
	 *
	 * Uncomment this function call to make the config file publishable using the 'config' tag.
	 */
	// $this->publishes([
	//     __DIR__.'/../../config/<%=PACKAGE_SLUG%>.php' => config_path('<%=PACKAGE_SLUG%>.php'),
	// ], 'config');

	/**
	 * Routes
	 *
	 * Uncomment this function call to load the route files.
	 * A web.php file has already been generated.
	 */
	$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

	/**
	 * Translations
	 *
	 * Uncomment the first function call to load the translations.
	 * Uncomment the second function call to load the JSON translations.
	 * Uncomment the third function call to make the translations publishable using the 'translations' tag.
	 */
	// $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', '<%=PACKAGE_SLUG%>');
	// $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang', '<%=PACKAGE_SLUG%>');
	// $this->publishes([
	//     __DIR__.'/../../resources/lang' => resource_path('lang/vendor/<%=PACKAGE_SLUG%>'),
	// ], 'translations');

	/**
	 * Views
	 *
	 * Uncomment the first section to load the views.
	 * Uncomment the second section to make the view publishable using the 'view' tags.
	 */
	// $this->loadViewsFrom(__DIR__.'/../../resources/views', '<%=PACKAGE_SLUG%>');
	// $this->publishes([
	//     __DIR__.'/../../resources/views' => resource_path('views/vendor/<%=PACKAGE_SLUG%>'),
	// ], 'views');

	/**
	 * Commands
	 *
	 * Uncomment this section to load the commands.
	 * A basic command file has already been generated in 'src\Console\Commands\MyPackageCommand.php'.
	 */
	// if ($this->app->runningInConsole()) {
	//     $this->commands([
	//         \<%=PACKAGE_NAMESPACE%>\Console\Commands\<%=CLASS_NAME%>Command::class,
	//     ]);
	// }

	/**
	 * Public assets
	 *
	 * Uncomment this functin call to make the public assets publishable using the 'public' tag.
	 */
	// $this->publishes([
	//     __DIR__.'/../../public' => public_path('vendor/<%=PACKAGE_SLUG%>'),
	// ], 'public');

	/**
	 * Migrations
	 *
	 * Uncomment the first function call to load the migrations.
	 * Uncomment the second function call to make the migrations publishable using the 'migrations' tags.
	 */
	// $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
	// $this->publishes([
	//     __DIR__.'/../../database/migrations/' => database_path('migrations')
	// ], 'migrations');

	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
{
	parent::register();

	$this->mergeConfigFrom(
		__DIR__ . '/../config/helpdesk.php',
		'helpdesk'
	);
	/**
	 * Config file
	 *
	 * Uncomment this function call to load the config file.
	 * If the config file is also publishable, it will merge with that file
	 */
	// $this->mergeConfigFrom(
	//     __DIR__.'/../../config/<%=PACKAGE_SLUG%>.php', '<%=PACKAGE_SLUG%>'
	// );
}


}
