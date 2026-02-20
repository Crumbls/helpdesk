<?php

namespace Crumbls\HelpDesk\Tests;

use Crumbls\HelpDesk\HelpDeskServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            HelpDeskServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('helpdesk.api.enabled', true);
        $app['config']->set('helpdesk.api.middleware', ['web']);

        $app['config']->set('helpdesk.guest.enabled', true);
        $app['config']->set('helpdesk.guest.middleware', ['web']);
        $app['config']->set('helpdesk.notifications.enabled', true);
        $app['config']->set('helpdesk.events.enabled', true);
        $app['config']->set('helpdesk.models.user', \Crumbls\HelpDesk\Tests\Fixtures\User::class);
        $app['config']->set('helpdesk.attachments.enabled', true);
        $app['config']->set('helpdesk.attachments.max_size_kb', 10240);
        $app['config']->set('helpdesk.attachments.allowed_mimes', ['jpg', 'png', 'pdf']);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }
}
