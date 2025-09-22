<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Uxmal\Backoffice\Layouts\MasterLayout;

class RootController extends Controller
{
    public function __invoke(Request $request, MasterLayout $layout){
        $this->buildMasterLayout($request, $layout);
        $layout->viteAsset('resources/js/app.js');

        // This is a custom component that is used in the gridjs component, insert the remix icon component
        //$layout->useComponent('ui-icon-remix');

        return $layout->render();
    }
}
