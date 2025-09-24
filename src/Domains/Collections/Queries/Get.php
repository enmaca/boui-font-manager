<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Queries;

use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;

#[RegisterQuery('/v1/font-manager/collections/get.gridjs', 'get', 'qry.font-manager.collections.get.v1')]
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
        $this->setQueryBuilder(
            FontCategory::withCount('fonts')
        )
            ->setQueryColumns([
                'id',
                'name',
                'description',
                'created_at',
                'fonts_count'
            ])
            ->setSearchColumns([
                'name',
                'description',
            ])
            ->setRenderColumns([
                'hash' => function ($row) {
                    return $row->hash;
                },
                'name' => function ($row) {
                    return '<strong>' . e($row->name) . '</strong>';
                },
                'description' => function ($row) {
                    return $row->description ? e($row->description) : '<em class="text-muted">Sin descripción</em>';
                },
                'fonts_count' => function ($row) {
                    $count = $row->fonts_count ?? 0;
                    $badge_style = $count > 0 ? BSStylesEnum::Success : BSStylesEnum::Secondary;
                    
                    return (string) Html::div()
                        ->class('text-center')
                        ->content(
                            Html::div()
                                ->class("badge bg-{$badge_style->value}")
                                ->content($count . ' ' . ($count === 1 ? 'tipografía' : 'tipografías'))
                        );
                },
                'created_at' => function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                },
                'action' => function ($row) {
                    return (string) Html::divFlex()
                        ->justify(DivFlexJustifyContentEnum::Center)
                        ->content([
                            Html::button('EditCollection-' . $row->hash)
                                ->class('btnEditCollection me-2')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Primary)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->attribute('data-collection-id', $row->id)
                                ->attribute('data-collection-name', $row->name)
                                ->attribute('data-collection-description', $row->description)
                                ->content(UI::icon()->ri('edit-line')),
                            Html::button('DeleteCollection-' . $row->hash)
                                ->class('btnDeleteCollection')
                                ->uxmalIgnore()
                                ->btnStyle(BSStylesEnum::Danger)
                                ->btnType(ButtonTypeEnum::Soft)
                                ->btnSize(ButtonSizeEnum::Small)
                                ->attribute('data-collection-id', $row->id)
                                ->attribute('data-collection-name', $row->name)
                                ->content(UI::icon()->ri('delete-bin-line')),
                        ]);
                },
            ]);

        return $this->response($request);
    }
}