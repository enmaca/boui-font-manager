<?php

namespace Enmaca\Backoffice\FontManager\UI\Collections;

use Enmaca\Backoffice\FontManager\UI\ContentInterface;
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
            self::buildCollectionsContent(),
        ];
    }

    /**
     * @throws RandomException
     * @throws BackofficeUiException
     * @throws Exception
     */
    private static function buildCollectionsContent(): string
    {
        return UI::gridJS('collections')
            ->queryEndPoint(
                NamedRouteAction::make('qry.font-manager.collections.get.v1')
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
                            ->uxActionOnJSEvent(MouseEventsEnum::CLICK, Dispatch::event('collections.table-settings')),
                        AddCollectionModal::getContent(),
                    ])
            )
            ->setColumns([
                GridColumn::rowSelection('hash'),
                GridColumn::html('Nombre', 'name')
                    ->resizable(true)
                    ->sort(),
                GridColumn::html('Descripción', 'description')
                    ->resizable(true)
                    ->sort(),
                GridColumn::html('Tipografías', 'fonts_count')
                    ->sort(),
                GridColumn::html('Fecha de creación', 'created_at')
                    ->sort(),
                GridColumn::action(),
            ])
            ->setPagination(GridPagination::create(10))
            ->strippedRows()
            ->hoverRows();
    }
}