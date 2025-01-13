<?php

namespace Enmaca\Backoffice\FontManager\App\VersionManager;

use Enmaca\Backoffice\FontManager\App\ContentInterface;
use Uxmal\Backoffice\Actions\Dispatch;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Exceptions\BackofficeUiException;
use Uxmal\Backoffice\Helpers\NamedRoute as NamedRouteAction;
use Uxmal\Backoffice\JavaScriptEvents\MouseEventsEnum;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\HtmlElement;
use Uxmal\Backoffice\UI\GridJS\Column as GridColumn;
use Uxmal\Backoffice\UI\GridJS\Pagination as GridPagination;

class Content implements ContentInterface
{
    /**
     * @throws BackofficeUiException
     */
    public static function getMainContent(string|int|null $id = null): array
    {
        return [
            self::buildMainContent($id),
        ];
    }

    /**
     * @throws BackofficeUiException
     */
    private static function buildMainContent(string|int $id): HtmlElement
    {

        return UI::gridJS('versions')
            ->queryEndPoint(
                NamedRouteAction::make('qry.font-manager.versions.get.v1', ['id' => $id])
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
                            ->uxActionOnJSEvent(MouseEventsEnum::CLICK, Dispatch::event('font-manager.versions.table-settings')),
                        // AddGoogleTypographyModal::getContent(),
                        // AddTypographyModal::getContent()
                    ])
            )
            ->setColumns([
                GridColumn::rowSelection('hash'),
                GridColumn::html('Version', 'version')
                    ->sort(),
                GridColumn::html('Comentarios', 'version_comments')
                    ->sort(),
                GridColumn::html('Fecha', 'created_at')
                    ->sort(),
                GridColumn::html('Estatus', 'status'),
            ])
            ->setPagination(GridPagination::create(5))
            ->strippedRows()
            ->hoverRows();
    }
}
