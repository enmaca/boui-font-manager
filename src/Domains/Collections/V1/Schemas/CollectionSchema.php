<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\V1\Schemas;

use OpenApi\Attributes as OA;

/**
 * OpenAPI schema definitions for Collection entities.
 *
 * Provides centralized schema definitions for all collection-related
 * API responses, ensuring consistency across endpoints.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\V1\Schemas
 */
class CollectionSchema
{
    #[OA\Schema(
        schema: 'Collection',
        title: 'Collection',
        description: 'Font collection/category entity',
        type: 'object',
        required: ['id', 'name', 'created_at'],
        properties: [
            new OA\Property(
                property: 'id',
                description: 'Unique identifier for the collection',
                type: 'integer',
                format: 'int64',
                example: 1
            ),
            new OA\Property(
                property: 'hash',
                description: 'Unique hash identifier for the collection',
                type: 'string',
                example: 'abc123def456'
            ),
            new OA\Property(
                property: 'name',
                description: 'Collection name',
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
            new OA\Property(
                property: 'fonts_count',
                description: 'Number of fonts assigned to this collection',
                type: 'integer',
                minimum: 0,
                example: 5
            ),
            new OA\Property(
                property: 'created_at',
                description: 'Collection creation timestamp',
                type: 'string',
                format: 'date-time',
                example: '2024-09-24T14:30:00Z'
            ),
            new OA\Property(
                property: 'updated_at',
                description: 'Collection last update timestamp',
                type: 'string',
                format: 'date-time',
                example: '2024-09-24T16:45:00Z'
            ),
            new OA\Property(
                property: 'fonts',
                description: 'Associated fonts (only when explicitly loaded)',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/CollectionFont'),
                nullable: true
            ),
        ]
    )]
    public function collection(): void {}

    #[OA\Schema(
        schema: 'CollectionFont',
        title: 'Collection Font',
        description: 'Font summary within a collection context',
        type: 'object',
        required: ['id', 'name', 'active'],
        properties: [
            new OA\Property(
                property: 'id',
                description: 'Font unique identifier',
                type: 'integer',
                format: 'int64',
                example: 1
            ),
            new OA\Property(
                property: 'hash',
                description: 'Font unique hash identifier',
                type: 'string',
                nullable: true,
                example: 'font123hash456'
            ),
            new OA\Property(
                property: 'name',
                description: 'Font name',
                type: 'string',
                example: 'Times New Roman'
            ),
            new OA\Property(
                property: 'active',
                description: 'Whether the font is active',
                type: 'boolean',
                example: true
            ),
            new OA\Property(
                property: 'tags',
                description: 'Font tags (optional)',
                type: 'array',
                items: new OA\Items(type: 'string'),
                nullable: true,
                example: ['serif', 'classic']
            ),
            new OA\Property(
                property: 'variants_count',
                description: 'Number of font variants (only in detailed view)',
                type: 'integer',
                minimum: 0,
                example: 4
            ),
        ]
    )]
    public function collectionFont(): void {}

    #[OA\Schema(
        schema: 'CollectionMinimal',
        title: 'Collection Minimal',
        description: 'Minimal collection representation for dropdowns and lists',
        type: 'object',
        required: ['id', 'name'],
        properties: [
            new OA\Property(
                property: 'id',
                description: 'Collection unique identifier',
                type: 'integer',
                format: 'int64',
                example: 1
            ),
            new OA\Property(
                property: 'hash',
                description: 'Collection unique hash identifier',
                type: 'string',
                example: 'abc123def456'
            ),
            new OA\Property(
                property: 'name',
                description: 'Collection name',
                type: 'string',
                example: 'Serif Clásicas'
            ),
            new OA\Property(
                property: 'fonts_count',
                description: 'Number of fonts in collection',
                type: 'integer',
                minimum: 0,
                example: 5
            ),
        ]
    )]
    public function collectionMinimal(): void {}

    #[OA\Schema(
        schema: 'CollectionGridResponse',
        title: 'Collection Grid Response',
        description: 'GridJS response for collections table',
        type: 'object',
        required: ['data', 'total'],
        properties: [
            new OA\Property(
                property: 'data',
                description: 'Collection data rows',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Collection')
            ),
            new OA\Property(
                property: 'total',
                description: 'Total number of collections',
                type: 'integer',
                minimum: 0,
                example: 25
            ),
            new OA\Property(
                property: 'page',
                description: 'Current page number',
                type: 'integer',
                minimum: 1,
                example: 1
            ),
            new OA\Property(
                property: 'per_page',
                description: 'Items per page',
                type: 'integer',
                minimum: 1,
                example: 10
            ),
        ]
    )]
    public function collectionGridResponse(): void {}

    #[OA\Schema(
        schema: 'ErrorResponse',
        title: 'Error Response',
        description: 'Standard error response format',
        type: 'object',
        required: ['success', 'message'],
        properties: [
            new OA\Property(
                property: 'success',
                description: 'Operation success status',
                type: 'boolean',
                example: false
            ),
            new OA\Property(
                property: 'message',
                description: 'Error message',
                type: 'string',
                example: 'Validation failed'
            ),
            new OA\Property(
                property: 'errors',
                description: 'Detailed validation errors',
                type: 'object',
                nullable: true,
                example: ['name' => ['The name field is required.']]
            ),
        ]
    )]
    public function errorResponse(): void {}

    #[OA\Schema(
        schema: 'SuccessResponse',
        title: 'Success Response',
        description: 'Standard success response format',
        type: 'object',
        required: ['success', 'message'],
        properties: [
            new OA\Property(
                property: 'success',
                description: 'Operation success status',
                type: 'boolean',
                example: true
            ),
            new OA\Property(
                property: 'message',
                description: 'Success message',
                type: 'string',
                example: 'Collection created successfully'
            ),
            new OA\Property(
                property: 'data',
                description: 'Response data (optional)',
                nullable: true
            ),
        ]
    )]
    public function successResponse(): void {}
}