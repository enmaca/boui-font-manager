<?php

namespace Enmaca\Backoffice\FontManager\UI\VersionManager;

use Enmaca\Backoffice\FontManager\UI\ContentInterface;
use Enmaca\Backoffice\FontManager\Models\FontVariant;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
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
    public static function getMainContent(string|null $type = null, string|int|null $id = null): array
    {
        if( $type === null || $id === null ){
            throw new BackofficeUiException('Type and ID are required');
        }

        return self::buildMainContent($type, $id);
    }

    /**
     * @throws BackofficeUiException
     */
    private static function buildMainContent(string $type, string|int $id) : array
    {

        if( !$type || !$id ){
            throw new BackofficeUiException('Type and ID are required');
        }

        $name = match ($type){
            'font-variant' => FontVariant::find(FontVariant::normalizeId($id))->font->name. ' ('.FontVariant::find(FontVariant::normalizeId($id))->sub_family.')',
            'google-fonts' => GoogleFontFiles::find(GoogleFontFiles::normalizeId($id))->family->family.' ('.GoogleFontFiles::find(GoogleFontFiles::normalizeId($id))->variant->name.')',
            default => null
        };


        return [
            Html::tag('div')
                ->content(
                    Html::tag('h3')
                        ->content($name)
                ),
            UI::gridJS('versions')
            ->queryEndPoint(
                NamedRouteAction::make('qry.font-manager.versions.get.v1', [
                    'type' => $type,
                    'id' => $id
                ])
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
            ->hoverRows()
            ];
    }
}
