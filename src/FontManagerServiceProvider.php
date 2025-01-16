<?php

namespace Enmaca\Backoffice\FontManager;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use ReflectionException;
use Uxmal\Backend\Helpers\RegisterCmdQry;

class FontManagerServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException|ReflectionException
     */
    public function register(): void
    {
        app()->make(RegisterCmdQry::class)->register(__DIR__.'/Domains/', [
            // 'middleware' => ['auth:sanctum'],
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../resources/js' => base_path('resources/js/product-designer/'),
            __DIR__.'/../resources/scss' => base_path('resources/scss/product-designer/'),
        ], 'product-designer-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Enmaca\Backoffice\FontManager\Console\BuildExternalDependenciesConsole::class,
                \Enmaca\Backoffice\FontManager\Console\UpdateGoogleFontsDatabaseConsole::class,
            ]);
        }

        // Livewire::component('component.name', ComponentName::class);

    }

    public function provides(): array
    {
        return [

        ];
    }
}
