<?php

namespace Enmaca\Backoffice\Typography;

use Illuminate\Support\Facades\App;

class TypographyResourceManager
{
    private static ?TypographyResourceManager $instance = null;

    private static string $composerNamespace = 'enmaca/boui-font-manager';
    private array $jsResources = [];

    private array $cssResources = [];

    private array $scssResources = [];

    private function __construct() {}

    public static function getInstance(): TypographyResourceManager
    {
        if (self::$instance === null) {
            self::$instance = new TypographyResourceManager;
        }

        return self::$instance;
    }

    public function addJSResource(string $resource): void
    {
        $this->jsResources[] = self::viteJSResource($resource);
    }

    public function getJSResources(): array
    {
        return $this->jsResources;
    }

    public function addCSSResource(string $resource): void
    {
        $this->cssResources[] = self::viteCSSResource($resource);
    }

    public function getCSSResources(): array
    {
        return $this->cssResources;
    }

    public function addSCSSResource(string $resource): void
    {
        $this->scssResources[] = self::viteSCSSResource($resource);
    }

    public function getSCSSResources(): array
    {
        return $this->scssResources;
    }

    private static function viteJSResource($resource): string
    {
        if (App::environment('local')) {
            return '@vite("vendor/'.self::$composerNamespace.'/resources/js/'.$resource.'")';
        }
        if (App::environment('production')) {
            return '@vite("resources/js/product-designer/'.$resource.'")';
        }

        return '';
    }

    private static function viteCSSResource($resource): string
    {
        if (App::environment('local')) {
            return '@vite("vendor/'.self::$composerNamespace.'/resources/css/'.$resource.'")';
        }
        if (App::environment('production')) {
            return '@vite("resources/css/product-designer/'.$resource.'")';
        }

        return '';
    }

    private static function viteSCSSResource($resource): string
    {
        if (App::environment('local')) {
            return '@vite("vendor/'.self::$composerNamespace.'/resources/scss/'.$resource.'")';
        }
        if (App::environment('production')) {
            return '@vite("resources/scss/product-designer/'.$resource.'")';
        }

        return '';
    }
}
