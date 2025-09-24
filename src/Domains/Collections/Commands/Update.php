<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Domains\V1\Resources\CollectionResource;
use Enmaca\Backoffice\FontManager\Models\FontCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Uxmal\Backend\Attributes\RegisterCommand;

/**
 * Command class for updating existing font collections.
 *
 * Handles the update of existing font collections/categories with proper
 * validation, unique name checking (excluding current record), error handling,
 * and response formatting.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\Commands
 */
#[RegisterCommand('/v1/font-manager/collections/update', 'put', 'cmd.font-manager.collections.update.v1')]
class Update
{
    /** Command name constant for tracking/logging purposes. */
    const COMMAND_NAME = 'cmd.font-manager.collections.update.v1';

    /** Resource class used for response transformation. */
    const RESOURCE = CollectionResource::class;

    public array $payloadValidator = [
        'id' => 'required|integer|exists:font_categories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * Update an existing font collection.
     *
     * Validates input data including unique name constraint (excluding current record),
     * updates the FontCategory instance, and returns the updated collection data.
     * Handles validation errors and database exceptions.
     *
     * @param Request $request The HTTP request containing collection update data
     * @return JsonResponse Success response with updated collection or error response
     *
     * @throws \Exception When collection update fails due to database errors
     */
    #[OA\Put(
        path: '/v1/font-manager/collections/update',
        description: 'Updates an existing font collection with the provided data',
        summary: 'Update existing collection',
        tags: ['Collections'],
        requestBody: new OA\RequestBody(
            description: 'Collection data to update',
            required: true,
            content: new OA\JsonContent(
                required: ['id', 'name'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        description: 'Collection ID to update',
                        type: 'integer',
                        format: 'int64',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'name',
                        description: 'Collection name (must be unique)',
                        type: 'string',
                        maxLength: 255,
                        example: 'Serif Clásicas Mejoradas'
                    ),
                    new OA\Property(
                        property: 'description',
                        description: 'Collection description (optional)',
                        type: 'string',
                        maxLength: 1000,
                        nullable: true,
                        example: 'Tipografías con remates tradicionales actualizadas'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Collection updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Colección actualizada exitosamente'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Collection'),
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
                description: 'Server error during collection update',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
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
            $collection = FontCollection::findOrFail($collectionId);

            $collection->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            // Use resource for consistent data transformation
            $resource = CollectionResource::fromModel($collection);

            return response()->json([
                'success' => true,
                'message' => 'Colección actualizada exitosamente',
                'data' => $resource->toArray()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Colección no encontrada'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error updating collection', [
                'command' => self::COMMAND_NAME,
                'collection_id' => $collectionId,
                'error' => $e->getMessage(),
                'input' => $request->only(['name', 'description'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}
