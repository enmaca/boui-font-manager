<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Uxmal\Backend\Attributes\RegisterCommand;

#[RegisterCommand('/v1/font-manager/collections/delete', 'delete', 'cmd.font-manager.collections.delete.v1')]
class Delete
{
    public array $payloadValidator = [
        'id' => 'required|integer|exists:font_categories,id',
    ];

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->payloadValidator);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = FontCategory::findOrFail($request->input('id'));
            
            // Check if collection has fonts assigned
            $fontsCount = $collection->fonts()->count();
            if ($fontsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar la colección porque tiene {$fontsCount} tipografía(s) asignada(s). Primero debe remover las tipografías de la colección."
                ], 400);
            }

            $collectionName = $collection->name;
            $collection->delete();

            return response()->json([
                'success' => true,
                'message' => "Colección '{$collectionName}' eliminada exitosamente",
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}