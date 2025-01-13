<?php

namespace Enmaca\Backoffice\FontManager\App\Catalog;

use Uxmal\Backoffice\Actions\Javascript as JavascriptAction;
use Uxmal\Backoffice\Actions\SubmitForm;
use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\JavaScriptEvents\MouseEventsEnum;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;

class AddGoogleTypographyModal
{
    public function __construct() {}

    public static function getContent(): string
    {
        return UI::modal('addGoogleTypography')
            ->title('Nueva Tipografía')
            ->body(
                Form::make(name: 'addGoogleTypography', action: 'cmd.pd.product.create.v1')
                    ->autocomplete(false)
                    ->content(
                        html::divRow()
                            ->class('g-3')
                            ->content([
                                html::div()
                                    ->class('col-12')
                                    ->content(
                                        Form::inputGroup('name')
                                            ->label('Nombre de la tipografía')
                                            ->required()
                                    ),
                                html::div()
                                    ->class('col-12')
                                    ->content(Form::inputGroup('width_mm')
                                        ->suffix('mm')
                                        ->label('Ancho (mm)')
                                        ->placeholder('210')
                                        ->required()
                                    ),
                                html::div()
                                    ->class('col-12')
                                    ->content(Form::inputGroup('height_mm')
                                        ->suffix('mm')
                                        ->label('Largo (mm)')
                                        ->placeholder('297')
                                        ->required()
                                    ),
                            ])
                    )
                    ->uxActionOnSuccessSubmit(JavascriptAction::call('onSuccessCreateNewDesign'))
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
                    ->uxActionOnJSEvent(MouseEventsEnum::CLICK, SubmitForm::target('addGoogleTypography')),
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
