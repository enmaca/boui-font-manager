<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Queries;

use Enmaca\Backoffice\FontManager\Models\Font;
use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;

#[RegisterQuery('/v1/font-manager/typography/get.gridjs', 'get', 'qry.font-manager.typography.get.v1')]
class Get
{
    use GridJSQueryResponse;

    public array $payloadValidator = [
        'search' => 'string',
    ];

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {

        $versionPath = $request->get('versionPath', '/font-manager/versions/');

        $this->setQueryBuilder(
            FontFiles::with(['font_origin'])
        )
            ->setQueryColumns([
                'id',
                'font_origin_type',
                'font_origin_id',
                'original_name',
                'version',
                'uri',
                'created_at'
            ])
            ->setSearchColumns([
                'name',
                'tags',
                'active',
            ])
            ->setRenderColumns([
                'hash' => function ($row) {
                    return $row->hash;
                },
                'name' => function ($row) {
                    return match ($row->font_origin_type) {
                        GoogleFontFiles::class => $row->font_origin->family->family. ' (' . $row->font_origin->variant->name.')',
                        default => 'unknown',
                    };
                },
                'preview' => function ($row) {

                    $fontUrl = match ($row->font_origin_type) {
                        GoogleFontFiles::class => $row->url(),
                        default => '',
                    };
                    $fontName = Str::camel($row->font_origin->family->family.'_'.$row->font_origin->variant->name);

                    return (string) Html::div()
                        ->class('font-preview')
                        ->style('font-family: '. $fontName)
                        ->style('font-size: 1.5rem')
                        ->dataSetUxmal('font-name', $fontName)
                        ->dataSetUxmal('font-url', $fontUrl)
                        ->content('Aa Bb Cc Dd Ee Ff Gg Hh Ii Jj Kk Ll Mm<br>Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz');
                },
                'version' => function ($row) {
                    return 'number: '.$row->version.'<br> comments: '.($row->version == 1 ? ' original font' : $row->version_comments).'<br>created on: '.$row->created_at;
                },
                'action' => function ($row) use ($versionPath) {
                    return (string) Html::divFlex()
                        ->justify(DivFlexJustifyContentEnum::Center)
                        ->content([
                            Html::button('EditTypography-'.$row->hash)
                                ->href('/font-edit/index.html?id='.$row->hash)
                                ->target('_blank')
                                ->style('margin-right : 10px')
                                ->class('btnEdit')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Primary)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->content(UI::icon()->ri('edit-line')),
                            Html::button('ViewVersions-'.$row->hash)
                                ->href($versionPath.$row->hash)
                                ->style('margin-right : 10px')
                                ->class('btnViewVersions')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Secondary)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->content(UI::icon()->ri('git-branch-line')),
                        ]);
                },
            ]);

        return $this->response($request);
    }
}
