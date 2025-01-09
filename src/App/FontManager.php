<?php

namespace Enmaca\Backoffice\Typography\App;

use Enmaca\Backoffice\Typography\TypographyResourceManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Random\RandomException;
use Uxmal\Backoffice\Actions\Dispatch;
use Uxmal\Backoffice\Actions\Javascript as JavascriptAction;
use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Exceptions\BackofficeUiException;
use Uxmal\Backoffice\Form\Input\Filepond\Options as FilepondOptions;
use Uxmal\Backoffice\Form\Input\Filepond\ServerOptions;
use Uxmal\Backoffice\Helpers\NamedRoute;
use Uxmal\Backoffice\Helpers\NamedRoute;
use Uxmal\Backoffice\Helpers\NamedRoute as NamedRouteAction;
use Uxmal\Backoffice\JavaScriptEvents\MouseEventsEnum;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\HtmlElement;
use Uxmal\Backoffice\UI\GridJS\Column as GridColumn;
use Uxmal\Backoffice\UI\GridJS\Pagination as GridPagination;
use Uxmal\Backoffice\UI\SideMenu;

class FontManager
{

    private TypographyResourceManager $resourceManager;

    private HtmlElement $content;

    public function __construct()
    {
        $this->resourceManager = app(TypographyResourceManager::class);
        $this->resourceManager->addJSResource('typography.js');
        if( App::environment('local') ) {
            $this->resourceManager->addSCSSResource('typography.scss');
        }

        if( App::environment('production') ) {
            $this->resourceManager->addSCSSResource('typography.css');
        }

        $this->content = Html::body('BodyFontManager');
    }

    public function getResources(): array
    {
        return [
            'js' => $this->resourceManager->getJSResources(),
            'css' => $this->resourceManager->getCSSResources(),
            'scss' => $this->resourceManager->getSCSSResources(),
        ];
    }


    /**
     * @throws RandomException
     * @throws BackofficeUiException
     */
    public function getContent(): array
    {
        return [
          $this->buildFontManager()
        ];
    }

    /**
     * @throws RandomException
     * @throws BackofficeUiException
     * @throws \Exception
     */
    private function buildFontManager(): string
    {
         $this->content->content(
             UI::gridJS('typography')
            ->queryEndPoint(
                NamedRouteAction::make('qry.pd.typography.get.v1')
            )
            ->setHeaderContent(
                Html::div()
                    ->class('d-flex justify-content-between gap-1')
                    ->content([
                        Html::button('tableSettings')
                            ->btnType(ButtonTypeEnum::Outline)
                            ->btnStyle(BSStylesEnum::Primary)
                            ->content(UI::icon()->ri('settings-2-line'))
                            ->btnSize(ButtonSizeEnum::Small)
                            ->uxActionOnJSEvent(MouseEventsEnum::CLICK, Dispatch::event('digital-product.table-settings')),
                        AddGoogleTypographyModal::getContent(),
                        AddTypographyModal::getContent()
                    ])
            )
            ->setColumns([
                GridColumn::rowSelection('hash'),
                GridColumn::html('Tipografía', 'name')
                    ->resizable(true)
                    ->sort(),
                GridColumn::html('Previsualización', 'preview')
                    ->sort(),
                GridColumn::html('Version', 'version')
                    ->sort(),
                GridColumn::plain('Estatus', 'status'),
                GridColumn::action(),
            ])
            ->setPagination(GridPagination::create(5))
            ->strippedRows()
            ->hoverRows()
         );

         return $this->content->render();
    }


    /**
     * @throws BindingResolutionException
     */
    public function getMenu(): string
    {
        return $this->buildMenu();
    }


    /**
     * @throws BindingResolutionException
     */
    private function buildMenu(): string
    {
        return SideMenu::item('Tipografías')
            ->icon(UI::icon()->ri('text'))
            ->items([
                SideMenu::item('Catálogo')
                    ->route('typography.catalog')
                    ->icon(UI::icon()->ri('function-line')),
                SideMenu::item('Catetgorías')
                    ->route('typography.category')
                    ->icon(UI::icon()->ri('stack-line')),
            ]);
    }
}
