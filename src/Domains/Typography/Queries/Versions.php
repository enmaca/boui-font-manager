<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Queries;

use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Enmaca\Backoffice\FontManager\Models\FontVariant;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Uxmal\Backend\Attributes\RegisterQuery;
use Uxmal\Backend\Query\Traits\GridJSQueryBuilderResponseTrait as GridJSQueryResponse;
use Uxmal\Backoffice\Components\Form;
use Uxmal\Backoffice\Components\Html;
use Uxmal\Backoffice\Support\Enums\DivFlexJustifyContentEnum;
use Uxmal\Backoffice\Support\Enums\ToggleSizeEnum;
use function PHPUnit\Framework\matches;

#[RegisterQuery('/v1/font-manager/typography/{type}/{id}/versions.gridjs', 'get', 'qry.font-manager.versions.get.v1')]
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
       $type = $request->route('type');
         $id = $request->route('id');

         if( !$type || !$id ){
             return response()->json(['error' => 'Type and ID are required'], 400);
         }

         $id = match ($type){
             'font-variant' => FontVariant::normalizeId($id),
             'google-fonts' => GoogleFontFiles::normalizeId($id),
             default => null
         };

         $type = match ($type){
             'font-variant' => FontVariant::class,
             'google-fonts' => GoogleFontFiles::class,
             default => null
         };

         $this->setQueryBuilder(
            FontFiles::where('font_origin_type',$type)
                ->where('font_origin_id', $id)
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
                            ->setOnLabel('Default')
                                ->setOnValue('1')
                            ->setOffLabel('Inactive')
                            ->setSize(ToggleSizeEnum::ExtraSmall)
                            ->value($row->default)
                        ]);
                },
            ]);

        return $this->response($request);
    }
}
