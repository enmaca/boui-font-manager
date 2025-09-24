<?php

namespace Enmaca\Backoffice\FontManager\UI\Collections;

use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\UI\Modal;

class AddCollectionModal
{
    public function __construct() {}

    public static function getContent(): string
    {
        return UI::modal('addCollection')
            ->modalOptions(Modal::SIZE_LG | Modal::CENTERED)
            ->title('Nueva Colección')
            ->body(
                Form::form('addCollectionForm')
                    ->content([
                        Html::div()
                            ->class('row')
                            ->content([
                                Html::div()
                                    ->class('col-12 mb-3')
                                    ->content(
                                        Form::inputText('collectionName')
                                            ->label('Nombre de la colección')
                                            ->placeholder('Ingrese el nombre de la colección')
                                            ->required()
                                            ->class('form-control')
                                    ),
                                Html::div()
                                    ->class('col-12 mb-3')
                                    ->content(
                                        Form::inputTextArea('collectionDescription')
                                            ->label('Descripción (opcional)')
                                            ->placeholder('Ingrese una descripción para la colección')
                                            ->class('form-control')
                                            ->attribute('rows', '3')
                                    ),
                            ])
                    ])
            )
            ->footer([
                Html::button('Close')
                    ->class('btn btn-light')
                    ->content('Cancelar')
                    ->attribute('data-bs-dismiss', 'modal'),
                Html::button('CreateCollection')
                    ->id('createCollectionBtn')
                    ->attribute('type', 'button')
                    ->class('btn btn-primary')
                    ->content('Crear Colección')
            ])
            ->trigger(
                Html::button('addCollection')
                    ->content([
                        UI::icon()->ri('add-line'),
                        'Nueva Colección',
                    ])
                    ->btnType(ButtonTypeEnum::Outline)
                    ->btnStyle(BSStylesEnum::Primary)
                    ->btnSize(ButtonSizeEnum::Small)
            );
    }
}