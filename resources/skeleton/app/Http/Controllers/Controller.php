<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Services\UxmalSkeletonAppService;
use Uxmal\Backoffice\Layouts\MasterLayout;
use Uxmal\Backoffice\UI\SideMenu;

abstract class Controller extends BaseController
{

    private array $registeredSideMenuItems = [];

    private array $registeredTopBarActions = [];

    public function __construct()
    {
        $this->registeredSideMenuItems  = app(UxmalSkeletonAppService::class)->getSideMenuItems();
        $this->registeredTopBarActions  = app(UxmalSkeletonAppService::class)->getTopBarActions();
    }



    final protected function getSideMenuItems(): array {
        return $this->registeredSideMenuItems;
    }

    final protected function buildMasterLayout(Request $request, MasterLayout $layout) {
        $sideMenu = array_merge([ SideMenu::title('MENU') ], $this->registeredSideMenuItems );

        $layout->setMenuItems($sideMenu);

        $topBarActions = $this->registeredTopBarActions;


        foreach( $topBarActions as $topBarAction ) {
            $layout->addTopBarActionButton($topBarAction);
        }
    }
}
