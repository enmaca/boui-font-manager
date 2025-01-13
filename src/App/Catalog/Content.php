<?php

namespace Enmaca\Backoffice\FontManager\App\Catalog;

use Enmaca\Backoffice\FontManager\App\ContentInterface;
use Exception;
use Random\RandomException;
use Uxmal\Backoffice\Actions\Dispatch;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Exceptions\BackofficeUiException;
use Uxmal\Backoffice\Helpers\NamedRoute as NamedRouteAction;
use Uxmal\Backoffice\JavaScriptEvents\MouseEventsEnum;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\UI\GridJS\Column as GridColumn;
use Uxmal\Backoffice\UI\GridJS\Pagination as GridPagination;

class Content implements ContentInterface
{
    public function __construct() {}

    /**
     * @throws RandomException
     * @throws BackofficeUiException
     */
    public static function getMainContent(): array
    {
        return [
            self::buildFontManagerContent(),
        ];
    }

    /**
     * @throws RandomException
     * @throws BackofficeUiException
     * @throws Exception
     */
    private static function buildFontManagerContent(): string
    {
        return UI::gridJS('typography')
            ->queryEndPoint(
                NamedRouteAction::make('qry.font-manager.typography.get.v1')
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
                        AddTypographyModal::getContent(),
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
            ->hoverRows();
    }
}
