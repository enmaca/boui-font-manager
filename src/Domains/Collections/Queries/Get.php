<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Queries;

use Enmaca\Backoffice\FontManager\Domains\Collections\V1\Resources\CollectionResource;
use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Components\UI;
use Uxmal\Backoffice\Support\Enums\BSStylesEnum;
use Uxmal\Backoffice\Support\Enums\ButtonSizeEnum;
use Uxmal\Backoffice\Support\Enums\ButtonTypeEnum;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;

/**
 * Query class for retrieving collections/categories for GridJS display.
 *
 * Provides endpoint for fetching font collections with proper formatting
 * for GridJS table display, including font counts, action buttons, and
 * search functionality. Uses eager loading to prevent N+1 query issues.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\Queries
 */
#[RegisterQuery('/v1/font-manager/collections/get.gridjs', 'get', 'qry.font-manager.collections.get.v1')]
class Get
{
    use GridJSQueryResponse;

    /** Query name constant for tracking/logging purposes. */
    const QUERY_NAME = 'qry.font-manager.collections.get.v1';

    /** Resource class used for data transformation. */
    const RESOURCE = CollectionResource::class;

    /** Metadata for consistent pagination responses. */
    const META = [
        'collection' => 'Collections',
        'type' => 'font-collection',
        'description' => 'Fetches a list of font collections for GridJS table display.',
    ];

    public array $payloadValidator = [
        'search' => 'string|nullable',
        'page' => 'integer|min:1',
        'per_page' => 'integer|min:1|max:100',
    ];

    /**
     * Retrieve font collections for GridJS table display.
     *
     * Fetches collections with font counts, formatted for GridJS display
     * including action buttons, badges, and proper HTML formatting.
     * Uses eager loading to prevent N+1 query issues.
     *
     * @param Request $request The HTTP request instance
     * @return JsonResponse Collection of font collections for GridJS
     *
     * @throws \Exception When collection fetching fails
     */
    #[OA\Get(
        path: '/v1/font-manager/collections/get.gridjs',
        description: 'Retrieves font collections formatted for GridJS table display',
        summary: 'Get collections for GridJS',
        tags: ['Collections'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Search term for filtering collections by name or description',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'serif')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of collections for GridJS',
                content: new OA\JsonContent(ref: '#/components/schemas/CollectionGridResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Server error while fetching collections',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
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
                        return $row->description 
                            ? e($row->description) 
                            : '<em class="text-muted">Sin descripción</em>';
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las colecciones: ' . $e->getMessage()
            ], 500);
        }
    }
}