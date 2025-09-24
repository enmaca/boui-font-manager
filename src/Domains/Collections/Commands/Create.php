<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Domains\Collections\V1\Resources\CollectionResource;
use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Uxmal\Backend\Attributes\RegisterCommand;

/**
 * Command class for creating new font collections.
 *
 * Handles the creation of new font collections/categories with proper
 * validation, error handling, and response formatting. Ensures unique
 * collection names and provides detailed error feedback.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\Commands
 */
#[RegisterCommand('/v1/font-manager/collections/create', 'post', 'cmd.font-manager.collections.create.v1')]
class Create
{
    /** Command name constant for tracking/logging purposes. */
    const COMMAND_NAME = 'cmd.font-manager.collections.create.v1';

    /** Resource class used for response transformation. */
    const RESOURCE = CollectionResource::class;

    public array $payloadValidator = [
        'name' => 'required|string|max:255|unique:font_categories,name',
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * Create a new font collection.
     *
     * Validates input data, creates a new FontCategory instance, and returns
     * the created collection data. Handles validation errors and database
     * exceptions with appropriate error responses.
     *
     * @param Request $request The HTTP request containing collection data
     * @return JsonResponse Success response with created collection or error response
     *
     * @throws \Exception When collection creation fails due to database errors
     */
    #[OA\Post(
        path: '/v1/font-manager/collections/create',
        description: 'Creates a new font collection with the provided name and optional description',
        summary: 'Create new collection',
        tags: ['Collections'],
        requestBody: new OA\RequestBody(
            description: 'Collection data to create',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        description: 'Collection name (must be unique)',
                        type: 'string',
                        maxLength: 255,
                        example: 'Serif Clásicas'
                    ),
                    new OA\Property(
                        property: 'description',
                        description: 'Collection description (optional)',
                        type: 'string',
                        maxLength: 1000,
                        nullable: true,
                        example: 'Tipografías con remates tradicionales para textos elegantes'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Collection created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Colección creada exitosamente'),
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
                response: 500,
                description: 'Server error during collection creation',
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
            $collection = FontCategory::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            // Use resource for consistent data transformation
            $resource = CollectionResource::fromModel($collection);

            return response()->json([
                'success' => true,
                'message' => 'Colección creada exitosamente',
                'data' => $resource->toArray()
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating collection', [
                'command' => self::COMMAND_NAME,
                'error' => $e->getMessage(),
                'input' => $request->only(['name', 'description'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}