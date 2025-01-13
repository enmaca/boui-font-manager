<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Queries;

use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;

#[RegisterQuery('/v1/font-manager/typography/{id}/versions.gridjs', 'get', 'qry.font-manager.versions.get.v1')]
class Versions
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
            FontFiles::FontId($request->route('id'))
        )
            ->setQueryColumns([
                'id',
                'version',
                'version_comments',
                'created_at',
            ])
            ->setSearchColumns([
                'version',
                'version_comments',
                'created_at',
            ])
            ->setRenderColumns([
                'hash' => function ($row) {
                    return $row->hash;
                },
                'version' => function ($row) {
                    return $row->version;
                },
                'version_comments' => function ($row) {
                    return $row->version_comments ?? 'Initial Version';
                },
                'created_at' => function ($row) {
                    return Carbon::parse($row->created_at)->format('Y-m-d H:i:s');
                },
                'status' => function ($row) {
                    return (string) Html::divFlex()
                        ->justify(DivFlexJustifyContentEnum::Center)
                        ->content([
                            Form::inputToggle('FontFile::'.$row->hash)
                            ->onlabel('Default')
                            ->offlabel('Inactive')
                        ]);
                },
            ]);

        return $this->response($request);
    }
}
