<?php

namespace Enmaca\Backoffice\FontManager\UI\Catalog;

use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Helpers\NamedRoute as NamedRouteAction;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\UI\GridJS\Column as GridColumn;
use Uxmal\Backoffice\UI\GridJS\Pagination as GridPagination;
use Uxmal\Backoffice\UI\Modal;

class AddGoogleTypographyModal
{
    public function __construct() {}

    public static function getContent(): string
    {
        return UI::modal('addGoogleTypography')
            ->modalOptions(Modal::SIZE_XL | Modal::CENTERED)
            ->title('Importar tipografía de Google')
            ->body(
                UI::gridJS('googleFontList')
                    ->queryEndPoint(
                        NamedRouteAction::make('qry.font-manager.google-fonts.get.v1')
                    )
                    ->setColumns([
                        GridColumn::html('Tipografía', 'family')
                            ->resizable(true)
                            ->sort(),
                        GridColumn::html('Previsualización', 'preview')
                            ->sort(),
                        GridColumn::html('Version', 'version')
                            ->sort(),
                        GridColumn::action(),
                    ])
                ->setPagination(GridPagination::create(5))
            )
            ->footer([
                Html::button('Close')
                    ->class('btn btn-light')
                    ->content('Close')
                    ->attribute('data-bs-dismiss', 'modal'),
                Html::button('Create')
                    ->id('miID')
                    ->attribute('type', 'submit')
                    ->class('btn btn-primary')
                    ->content('Create')
                    //->uxActionOnJSEvent(MouseEventsEnum::CLICK, SubmitForm::target('addGoogleTypography')),
            ])
            ->trigger(
                Html::button('addGoogleTypography')
                    ->content([
                        UI::icon()->ri('add-line'),
                        'Agregar tipografía de Google',
                    ])
                    ->btnType(ButtonTypeEnum::Outline)
                    ->btnStyle(BSStylesEnum::Primary)
                    ->btnSize(ButtonSizeEnum::Small)
            );

    }
}
