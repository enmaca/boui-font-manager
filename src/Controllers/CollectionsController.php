<?php

namespace Enmaca\Backoffice\FontManager\Controllers;

use Exception;
use Illuminate\Http\Request;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Exceptions\BackofficeUiException;
use Uxmal\Backoffice\Layouts\MasterLayout;
use Enmaca\Backoffice\FontManager\UI\Collections\Content as FontManagerCollectionsContent;

class CollectionsController extends \App\Http\Controllers\Controller
{

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, MasterLayout $layout): string
    {
        if( method_exists($this, 'buildMasterLayout') ) {
            $this->buildMasterLayout($request, $layout);
        }

        $layout->setTitle('Colecciones de Tipografías');

        $layout->setTopBarContent(Html::divFlex()
            ->class('align-items-center w-100')
            ->content([
                Html::div()
                    ->class('text-start w-75 ps-5')
                    ->content(
                        Html::tag('h4')
                            ->content('Colecciones')
                    )
            ]));

        try {
            $layout->content(
                UI::card('collectionsCard')
                    ->body(
                        FontManagerCollectionsContent::getMainContent()
                    )
            );
        } catch (BackofficeUiException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        $layout->viteAsset('resources/js/collections.js');
        return $layout->render();
    }
}