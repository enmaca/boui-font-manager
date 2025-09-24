<?php

namespace Enmaca\Backoffice\FontManager\Domains\Collections\V1\Resources;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface for all collection resources.
 *
 * Provides standard methods for data transformation and localization
 * across all collection-related API resources.
 *
 * @package Enmaca\Backoffice\FontManager\Domains\Collections\V1\Resources
 */
interface ResourceInterface
{
    /**
     * Create a resource instance from an Eloquent model.
     *
     * @param Model $model The source model to transform
     * @param string $lang Language code for localization (default: 'es')
     * @return static New resource instance
     */
    public static function fromModel(Model $model, string $lang = 'es'): static;

    /**
     * Transform the resource into an array representation.
     *
     * @return array The resource data as an associative array
     */
    public function toArray(): array;
}