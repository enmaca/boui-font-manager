<?php

namespace Enmaca\Backoffice\FontManager\UI\Catalog;

use Uxmal\Backoffice\Actions\Javascript as JavascriptAction;
use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Form\Input\Filepond\Options as FilepondOptions;
use Uxmal\Backoffice\Form\Input\Filepond\ServerOptions;
use Uxmal\Backoffice\Helpers\NamedRoute;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;

class AddTypographyModal
{
    public function __construct() {}

    public static function getContent(): string
    {
        return UI::modal('addTypography')
            ->title('Agregar Tipografía')
            ->body(
                Form::make(name: 'uploadTypography', action: 'cmd.pd.product.create.v1')
                    ->autocomplete(false)
                    ->content([
                        Form::file('file')
                            ->containerRender(fn ($container) => $container->class('mb-3'))
                            ->filepond(
                                (new FilepondOptions)
                                    ->setAllowDrop(true)
                                    ->setServer(
                                        (new ServerOptions)
                                            ->setProcess(
                                                NamedRoute::make('cmd.font-manager.typography.file.process.v1', [
                                                    'sync' => true,
                                                ])
                                            )
                                            ->setRevert(
                                                NamedRoute::make('cmd.font-manager.typography.file.revert.v1', [
                                                    'sync' => true,
                                                ])
                                            )
                                    )
                            )
                            ->required(),
                        Form::text('typographyName')
                            ->label('Nombre')
                            ->disabled(),
                        Form::text('typographySubFamily')
                            ->label('Familia')
                            ->disabled(),
                        Form::text('typographyVersion')
                            ->label('Version')
                            ->disabled(),
                        Form::text('typographyPostScriptName')
                            ->label('PostScript')
                            ->disabled(),
                        Form::text('typographyCopyright')
                            ->label('CopyRight')
                            ->disabled(),
                    ]
                    )
                    ->uxActionOnSuccessSubmit(JavascriptAction::call('onSuccessCreateNewDesign'))
            )
            ->footer([
                Html::button('Close')
                    ->class('btn btn-light')
                    ->content('Close')
                    ->attribute('data-bs-dismiss', 'modal'),
            ])
            ->trigger(
                HTML::button('addTypography')
                    ->content([
                        UI::icon()->ri('add-line'),
                        'Agregar tipografía',
                    ])
                    ->btnType(ButtonTypeEnum::Normal)
                    ->btnStyle(BSStylesEnum::Primary)
                    ->btnSize(ButtonSizeEnum::Small)
            );
    }
}
