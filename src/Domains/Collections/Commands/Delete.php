<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Models\FontCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Uxmal\Backend\Attributes\RegisterCommand;

/**
 * Command class for deleting font collections.
 *
 * Handles the deletion of font collections/categories with proper validation
 * and business logic checks. Prevents deletion of collections that have
 * assigned fonts to maintain data integrity.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\Commands
 */
#[RegisterCommand('/v1/font-manager/collections/delete', 'delete', 'cmd.font-manager.collections.delete.v1')]
class Delete
{
    /** Command name constant for tracking/logging purposes. */
    const COMMAND_NAME = 'cmd.font-manager.collections.delete.v1';

    public array $payloadValidator = [
        'id' => 'required|integer|exists:font_collections,id',
    ];

    /**
     * Delete a font collection.
     *
     * Validates the collection exists, checks if it has assigned fonts
     * (preventing deletion if so), and removes the collection if safe.
     * Provides detailed error messages for business rule violations.
     *
     * @param Request $request The HTTP request containing collection ID
     * @return JsonResponse Success response or error response with details
     *
     * @throws \Exception When collection deletion fails due to database errors
     */
    #[OA\Delete(
        path: '/v1/font-manager/collections/delete',
        description: 'Deletes a font collection if it has no assigned fonts',
        summary: 'Delete collection',
        tags: ['Collections'],
        requestBody: new OA\RequestBody(
            description: 'Collection ID to delete',
            required: true,
            content: new OA\JsonContent(
                required: ['id'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        description: 'Collection ID to delete',
                        type: 'integer',
                        format: 'int64',
                        example: 1
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Collection deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: "Colección 'Serif Clásicas' eliminada exitosamente"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Collection has assigned fonts and cannot be deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'No se puede eliminar la colección porque tiene 5 tipografía(s) asignada(s)'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Collection not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Server error during collection deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
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
            $collection = FontCollection::findOrFail($request->input('id'));

            // Check business rule: collection must not have assigned fonts
            $fontsCount = $collection->fonts()->count();
            if ($fontsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar la colección porque tiene {$fontsCount} tipografía(s) asignada(s). Primero debe remover las tipografías de la colección."
                ], 400);
            }

            $collectionName = $collection->name;

            // Soft delete would be better for audit trail, but using hard delete as per current schema
            $collection->delete();

            \Log::info('Collection deleted successfully', [
                'command' => self::COMMAND_NAME,
                'collection_id' => $collection->id,
                'collection_name' => $collectionName,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Colección '{$collectionName}' eliminada exitosamente",
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Colección no encontrada'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting collection', [
                'command' => self::COMMAND_NAME,
                'collection_id' => $request->input('id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}
