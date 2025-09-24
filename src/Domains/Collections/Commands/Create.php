<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\Commands;

use Enmaca\Backoffice\FontManager\Models\FontCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Uxmal\Backend\Attributes\RegisterCommand;

#[RegisterCommand('/v1/font-manager/collections/create', 'post', 'cmd.font-manager.collections.create.v1')]
class Create
{
    public array $payloadValidator = [
        'name' => 'required|string|max:255|unique:font_categories,name',
        'description' => 'nullable|string|max:1000',
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
            $collection = FontCategory::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Colección creada exitosamente',
                'data' => [
                    'id' => $collection->id,
                    'hash' => $collection->hash,
                    'name' => $collection->name,
                    'description' => $collection->description,
                    'created_at' => $collection->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la colección: ' . $e->getMessage()
            ], 500);
        }
    }
}