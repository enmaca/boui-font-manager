<?php

namespace Enmaca\Backoffice\FontManager\Domains\V1\Resources;

use Enmaca\Backoffice\FontManager\Models\FontCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource class for transforming FontCategory models.
 *
 * Provides consistent data transformation for collection/category entities
 * across all API endpoints, including localization support and
 * relationship data.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\V1\Resources
 */
class CollectionResource implements ResourceInterface
{
    /**
     * The FontCollection model instance.
     */
    protected FontCollection $model;

    /**
     * Language code for localization.
     */
    protected string $lang;

    /**
     * Create a new CollectionResource instance.
     *
     * @param FontCollection $model The font collection model
     * @param string $lang Language code for localization
     */
    public function __construct(FontCollection $model, string $lang = 'es')
    {
        $this->model = $model;
        $this->lang = $lang;
    }

    /**
     * Create a resource instance from an Eloquent model.
     *
     * @param Model $model The FontCollection model to transform
     * @param string $lang Language code for localization (default: 'es')
     * @return static New CollectionResource instance
     * @throws \InvalidArgumentException If model is not a FontCollection instance
     */
    public static function fromModel(Model $model, string $lang = 'es'): static
    {
        if (!$model instanceof FontCollection) {
            throw new \InvalidArgumentException('Model must be an instance of FontCollection');
        }

        return new static($model, $lang);
    }

    /**
     * Transform the resource into an array representation.
     *
     * @return array The collection data as an associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->model->id,
            'hash' => $this->model->hash,
            'name' => $this->model->name,
            'description' => $this->model->description,
            'fonts_count' => $this->model->fonts_count ?? $this->model->fonts()->count(),
            'created_at' => $this->model->created_at?->toISOString(),
            'updated_at' => $this->model->updated_at?->toISOString(),
            'fonts' => $this->model->relationLoaded('fonts')
                ? $this->model->fonts->map(fn($font) => [
                    'id' => $font->id,
                    'name' => $font->name,
                    'active' => $font->active,
                ])
                : null,
        ];
    }

    /**
     * Get a minimal representation of the collection.
     *
     * Used for dropdown lists and other compact displays.
     *
     * @return array Minimal collection data
     */
    public function toMinimal(): array
    {
        return [
            'id' => $this->model->id,
            'hash' => $this->model->hash,
            'name' => $this->model->name,
            'fonts_count' => $this->model->fonts_count ?? 0,
        ];
    }

    /**
     * Get a detailed representation including all relationships.
     *
     * Used for single collection views and detailed operations.
     *
     * @return array Detailed collection data with relationships
     */
    public function toDetailed(): array
    {
        $data = $this->toArray();

        // Always load fonts relationship for detailed view
        if (!$this->model->relationLoaded('fonts')) {
            $this->model->load('fonts');
        }

        $data['fonts'] = $this->model->fonts->map(fn($font) => [
            'id' => $font->id,
            'hash' => $font->hash ?? null,
            'name' => $font->name,
            'active' => $font->active,
            'tags' => $font->tags,
            'variants_count' => $font->variants()->count(),
        ]);

        return $data;
    }
}
