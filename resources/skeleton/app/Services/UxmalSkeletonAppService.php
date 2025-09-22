<?php

namespace App\Services;


use Uxmal\Backoffice\Layouts\MasterLayout;
use Uxmal\Backoffice\Support\HtmlElement;

class UxmalSkeletonAppService
{

    private array $sideMenuItems = [];
    private array $topBarActions = [];

    /**
     * Register any application services.
     */
    public function getLayout(): string
    {
        return MasterLayout::class;
    }

    /**
     * Bootstrap any application services.
     */
    public function register(object $object): void
    {
        $class = get_class($object);

        if ($class === \Uxmal\Backoffice\UI\SideMenu\Item::class) {
            $this->addToSideMenu([$object]);
        } elseif ($class === \Uxmal\Backoffice\Layouts\MasterLayout\TopBarActionButton::class) {
            $this->addTopBarAction([$object]);
        }
    }

    private function addToSideMenu(array $items): void
    {
        $this->sideMenuItems = array_merge($this->sideMenuItems, $items);
    }

    public function getSideMenuItems(): array
    {
        return $this->sideMenuItems;
    }


    private function addTopBarAction(array $items): void
    {
        $this->topBarActions = array_merge($this->topBarActions, $items);
    }

    public function getTopBarActions(): array
    {
        return $this->topBarActions;
    }

}
