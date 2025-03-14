<?php

namespace Enmaca\Backoffice\FontManager;

use Enmaca\Backoffice\FontManager\UI\MasterLayoutSideMenu;
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

        if (class_exists('App\Services\UxmalSkeletonAppService') && !$this->app->bound('App\Services\UxmalSkeletonAppService')) {
            $this->app->singleton(\App\Services\UxmalSkeletonAppService::class, function () {
                return new \App\Services\UxmalSkeletonAppService();
            });
        } else {
            // Handle the case where UxmalSkeletonAppService does NOT exist
            // Log an error, or any other fallback mechanism
            // Example:
            \Log::warning('UxmalSkeletonAppService class not found. Skipping registration.');
        }


        app()->make(RegisterCmdQry::class)->register(__DIR__.'/Domains/', [
            // 'middleware' => ['auth:sanctum'],
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (class_exists('App\Services\UxmalSkeletonAppService') && $this->app->bound('App\Services\UxmalSkeletonAppService')) {
            $uxmalService = $this->app->make(\App\Services\UxmalSkeletonAppService::class);
            if (method_exists($uxmalService, 'register')) {
                $uxmalService->register(MasterLayoutSideMenu::get());
            }
        }

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
