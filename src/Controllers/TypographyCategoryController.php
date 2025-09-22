<?php

namespace Enmaca\Backoffice\FontManager\Controllers;

use Illuminate\Http\Request;
use Uxmal\Backoffice\Layouts\MasterLayout;

class TypographyCategoryController extends \App\Http\Controllers\Controller
{

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, MasterLayout $layout): string
    {
        if( method_exists($this, 'buildMasterLayout') ) {
            $this->buildMasterLayout($request, $layout);
        }

        return $layout->render();
    }
}
