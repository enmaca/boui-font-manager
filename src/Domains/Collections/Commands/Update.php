<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Uxmal\Backend\Attributes\RegisterCommand;

#[RegisterCommand('/v1/font-manager/collections/update', 'put', 'cmd.font-manager.collections.update.v1')]
class Update
{
    public array $payloadValidator = [
        'id' => 'required|integer|exists:font_categories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $collectionId = $request->input('id');
        
        // Add unique validation excluding current record
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:font_categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('font_categories', 'name')->ignore($collectionId)
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = FontCategory::findOrFail($collectionId);
            
            $collection->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Colección actualizada exitosamente',
                'data' => [
                    'id' => $collection->id,
                    'hash' => $collection->hash,
                    'name' => $collection->name,
                    'description' => $collection->description,
                    'updated_at' => $collection->updated_at->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}