<?php

namespace Enmaca\Backoffice\FontManager\UI;

use Illuminate\Contracts\Container\BindingResolutionException;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Support\HtmlElement;
use Uxmal\Backoffice\UI\SideMenu;

class MasterLayoutSideMenu
{
    /**
     * @throws BindingResolutionException
     */
    public static function get(): HtmlElement
    {
        return self::buildMenu();
    }

    /**
     * @throws BindingResolutionException
     */
    private static function buildMenu(): HtmlElement
    {
        return SideMenu::item('Tipografías')
            ->icon(UI::icon()->ri('text'))
            ->items([
                SideMenu::item('Catálogo')
                    ->route('enmaca.font-manager.catalog')
                    ->icon(UI::icon()->ri('function-line')),
                SideMenu::item('Catetgorías')
                    ->route('enmaca.font-manager.category')
                    ->icon(UI::icon()->ri('stack-line')),
            ]);
    }
}
