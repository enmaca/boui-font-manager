<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Queries;

use Enmaca\Backoffice\FontManager\Models\GoogleFontFamilies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Actions\Dispatch;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\JavaScriptEvents\MouseEventsEnum;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;
use Uxmal\Backoffice\Support\Enums\UITabTypeEnum;
use Uxmal\Backoffice\UI\Tab;

#[RegisterQuery('/v1/font-manager/google-fonts/get.gridjs', 'get', 'qry.font-manager.google-fonts.get.v1')]
class GoogleFonts
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

        $searchString = $request->get('search');
        if ($searchString == null || $searchString == '') {
            $searchItems = [];
        } else {
            $searchItems = explode(',', $searchString);
        }

        $query = GoogleFontFamilies::with(['tags', 'files']);

        // Check if there are search items to filter the query
        if (!empty($searchItems)) {
            // Apply filters to the query based on search items
            $query->where(function ($query) use ($searchItems) {
                // Filter by tags
                $query->whereHas('tags', function ($query) use ($searchItems) {
                    $query->whereIn('name', $searchItems);
                })
                    // Filter by category
                    ->orWhereIn('category', $searchItems);

                // Filter by family name
                foreach ($searchItems as $item) {
                    $query->orWhere('family', 'like', '%' . $item . '%');
                }
            });
        }

        $this->setQueryBuilder($query)
            ->setQueryColumns([
                'id',
                'family',
                'category',
                'subsets',
                'version'
            ])
            ->setSearchColumns([])
            ->setRenderColumns([
                'hash' => function ($row) {
                    return $row->hash;
                },
                'family' => function ($row) {

                    $tagsData = $row->tags->toArray() ?? [];

                    if (!empty($tagsData)) {
                        $tags = array_map(function ($tag) {
                            return $tag['name'];
                        }, $tagsData);
                    } else {
                        $tags = [];
                    }


                    return $row->family . '<br>' . $row->category; //.'<br>Tags: ('.implode(",", $tags).')';
                },
                'preview' => function ($row) {
                    $tabMain = UI::tab(str::camel('tab_' . $row->family))->tabType(UITabTypeEnum::Tabs);

                    $tabsNames = [];
                    $tabsPanes = [];
                    foreach ($row->files as $file) {
                        $tabsNames[$file->variant->name] = $file->variant->name . (($file->downloaded == 1) ? '&nbsp;' . UI::icon()->ri('checkbox-circle-line') : '');
                        $tabsPanes[$file->variant->name] = (string)Html::div()
                            ->class(['font-preview', 'd-block'])
                            ->style('font-family: ' . $row->family . '_' . $file->variant->name)
                            ->style('font-size: 1.5rem')
                            ->dataSetUxmal('font-name', $row->family . '_' . $file->variant->name)
                            ->dataSetUxmal('font-url', $file->remote_uri)
                            ->content(
                                Html::divFlex()
                                    ->content(
                                        [
                                            Html::div()
                                                ->content('Aa Bb Cc Dd Ee Ff Gg Hh Ii Jj Kk Ll Mm<br>Nn Oo Pp Qq Rr Ss Tt Uu Vv Ww Xx Yy Zz'),
                                            Html::div()
                                                ->class('align-items-middle ps-5')
                                                ->content(
                                                    (($file->downloaded !== 1) ? Html::button('DownloadCloud-' . $row->hash)
                                                        ->btnStyle(BSStylesEnum::Primary)
                                                        ->btnType(ButtonTypeEnum::Soft)
                                                        ->btnSize(ButtonSizeEnum::Small)
                                                        ->dataset('google-font-files-id', $file->hash)
                                                        ->content(UI::icon()->ri('download-cloud-line'))
                                                        ->uxActionOnJSEvent(MouseEventsEnum::CLICK, Dispatch::event('download.google-font')) : '')
                                                )
                                        ]
                                    )
                            );
                    }
                    $tabMain->tabs($tabsNames)->panes($tabsPanes);

                    return (string)$tabMain;
                },
                'version' => function ($row) {
                    return $row->version;
                },
                'action' => function ($row) {
                    $fontsDownloaded = 0;
                    $fontsCount = count($row->files);
                    foreach ($row->files as $file) {
                        if ($file->downloaded === 1) {
                            $fontsDownloaded++;
                        }
                    }
                    if ($fontsDownloaded === $fontsCount) {
                        $buttons = [
                            Html::button('AllDownloaded-' . $row->hash)
                                ->style('margin-right : 10px')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Success)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->content(UI::icon()->ri('checkbox-circle-line'))
                        ];
                    } else {
                        $buttons = [
                            Html::button('DownloadAll-' . $row->hash)
                                ->style('margin-right : 10px')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Primary)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->content(UI::icon()->ri('download-cloud-line'))
                                ->uxActionOnJSEvent(MouseEventsEnum::CLICK, Dispatch::event('download-all.google-font', ['google_font_family_id' => $row->hash]))
                        ];
                    }
                    return (string)Html::divFlex()
                        ->justify(DivFlexJustifyContentEnum::Center)
                        ->content($buttons);
                },
            ]);

        return $this->response($request);
    }
}
