<?php

namespace Enmaca\Backoffice\Typography;

use Enmaca\Backoffice\ProductDesigner\Layouts\ProductDesignerLayout;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ReflectionException;
use Uxmal\Backend\Helpers\RegisterCmdQry;

class TypographyServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException|ReflectionException
     */
    public function register(): void
    {
        app()->make(RegisterCmdQry::class)->register(__DIR__.'/Domains/', [
            //'middleware' => ['auth:sanctum'],
        ]);

        $this->app->singleton(TypographyResourceManager::class, function ($app) {
            return TypographyResourceManager::getInstance();
        });
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
                \Enmaca\Backoffice\ProductDesigner\Console\UpdateFontLibraryConsole::class,
                \Enmaca\Backoffice\ProductDesigner\Console\BuildExternalDependenciesConsole::class
            ]);
        }

        Livewire::component('elements.side-bar.aside-nav-item-list', \Enmaca\Backoffice\ProductDesigner\Elements\SideBar\Livewire\AsideNavItemList::class);
        Livewire::component('elements.layers-section', \Enmaca\Backoffice\ProductDesigner\Elements\Layers\Livewire\LayersSection::class);
        Livewire::component('elements.side-bar.context-tab', \Enmaca\Backoffice\ProductDesigner\Elements\SideBar\Livewire\ContextTab::class);
        Livewire::component('elements.side-bar.actions.image.my-image-collection', \Enmaca\Backoffice\ProductDesigner\Elements\SideBar\Actions\Livewire\Image\MyImageCollection::class);

    }

    public function provides(): array
    {
        return [
            ProductDesignerResourceManager::class,
            ProductDesignerLayout::class,
        ];
    }
}
