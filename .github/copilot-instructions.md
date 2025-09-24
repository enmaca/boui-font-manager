# GitHub Copilot Instructions

## Repository overview
- **Package goal:** Laravel package that extends the uxmaltech Backoffice UI ecosystem to manage typography assets (catalogs, categories, versioning) inside backoffice experiences.【F:composer.json†L2-L50】【F:src/FontManagerServiceProvider.php†L48-L60】
- **Dev workflow:** A Makefile-driven Docker environment bootstraps a disposable Laravel app, publishes package assets, and exposes Laravel/Vite dev servers for rapid iteration.【F:README.md†L1-L160】

## Tech stack & dependencies
- PHP 8.2+/Laravel components, Livewire 3, Symfony tooling, Guzzle, DOMPDF font utilities, and uxmaltech packages (`core`, `backoffice-ui`, `backend-cbq`).【F:composer.json†L35-L51】
- JavaScript assets rely on the global `boui` runtime provided by `uxmaltech/backoffice-ui` and Vite builds published through the package service provider.【F:src/FontManagerServiceProvider.php†L48-L55】【F:resources/js/catalog.js†L1-L66】

## Architecture tour
### Service provider & bootstrap
- `FontManagerServiceProvider` wires optional skeleton services, registers CQRS commands/queries from `src/Domains`, loads routes & migrations, publishes JS assets, and registers artisan commands when running in console.【F:src/FontManagerServiceProvider.php†L16-L60】

### Domain registration
- Domain APIs live under `src/Domains`; `RegisterCmdQry` scans this directory to expose command/query endpoints with OpenAPI metadata starting from `BaseQueryV1`. Extend these classes to surface new backend operations.【F:src/FontManagerServiceProvider.php†L31-L34】【F:src/Domains/BaseQueryV1.php†L3-L16】

### HTTP controllers & layouts
- Controllers extend the host Laravel app controller base, compose Backoffice UI layouts, and render cards/grids via fluent builders (e.g., `TypographyCatalogController`). Inject `MasterLayout` to configure chrome and register required Vite assets before returning rendered HTML.【F:src/Controllers/TypographyCatalogController.php†L13-L50】

### UI composition layer
- UI fragments implement `ContentInterface::getMainContent()` and typically return arrays of fluent Backoffice UI builders (cards, GridJS tables, modals, actions). Keep composition declarative and reuse helper classes such as `Html`, `UI`, and action enums as shown in `UI/Catalog/Content`.【F:src/UI/ContentInterface.php†L3-L7】【F:src/UI/Catalog/Content.php†L20-L75】

### Front-end integration
- JavaScript modules inside `resources/js` register Boui event handlers, use CBQ for backend calls, and refresh UI components (`boui.get(...).reload()`). Follow the event-driven patterns already in `catalog.js` when wiring new interactions.【F:resources/js/catalog.js†L5-L63】

### Console tooling
- Package commands such as `boui-font-manager:build-external-dependencies` orchestrate cloning/building external font editors and asset publishing. Mirror this pattern (signature, description, `handle()` orchestration) when creating new artisan tooling.【F:src/Console/BuildExternalDependenciesConsole.php†L8-L109】

### Routing
- HTTP entry points are defined in `routes/web.php` and use single-action controllers; register additional routes within the service provider context and keep naming consistent with the `enmaca.font-manager.*` namespace.【F:routes/web.php†L7-L13】

## Assets & publishing
- JS bundles under `resources/js/app-assets` are published to the host app via the `public` tag in the service provider; additional static assets (e.g., Glyphr Studio build artifacts) are produced by console commands into `public/font-edit`. Align new asset paths with these publishing hooks.【F:src/FontManagerServiceProvider.php†L48-L60】【F:src/Console/BuildExternalDependenciesConsole.php†L31-L68】

## Development workflow
1. Run `make start-dev-env` to build the Docker image, scaffold the throwaway Laravel app, link the package, and start Laravel/Vite servers.【F:README.md†L34-L112】
2. Provide `GITHUB_TOKEN` and `GOOGLE_FONTS_API_KEY` via environment or `.env` to unlock composer installs and Google Fonts sync tooling.【F:README.md†L7-L139】
3. Use `make up` / `make down` for iterative work; the dev app is ephemeral and can be reset with `make clean-dev-env`.【F:README.md†L80-L139】

## Copilot prompting tips
- **Reuse specialized instructions:** When editing Backoffice UI PHP builders or layouts, include `.github/instructions/backoffice-ui-php.instructions.md` to guide Copilot towards fluent APIs and resource registration patterns.【F:.github/instructions/backoffice-ui-php.instructions.md†L1-L24】
- **For Boui front-end work:** Reference `.github/instructions/backoffice-ui-boui.instructions.md` to remind Copilot about `boui.waitFor`, event naming, declarative actions, and initialization best practices before generating JS/HTML snippets.【F:.github/instructions/backoffice-ui-boui.instructions.md†L1-L56】
- **Context to share with Copilot:**
  - Identify the target layer (service provider, domain, controller, UI builder, JS module) and surface the relevant file or method signature.
  - Describe the desired outcome declaratively (e.g., “add a modal that dispatches a Boui event when confirmed”) so Copilot leverages fluent builders and CBQ helpers instead of manual HTML/JS glue.【F:.github/instructions/backoffice-ui-php.instructions.md†L19-L23】
  - Mention supporting assets or routes that need to be registered to ensure Copilot adds `viteAsset` calls, publishes resources, or updates route maps accordingly.【F:src/FontManagerServiceProvider.php†L48-L60】【F:src/Controllers/TypographyCatalogController.php†L39-L50】

## Quality & testing expectations
- Prefer extending existing builders and helpers over manual markup; they provide consistent styling and data attributes expected by Boui. This keeps new features aligned with Backoffice UI semantics.【F:.github/instructions/backoffice-ui-php.instructions.md†L5-L33】
- After significant JS changes, confirm events/actions align with patterns in `catalog.js` and leverage Boui logging helpers from the instructions to debug during development.【F:resources/js/catalog.js†L5-L63】【F:.github/instructions/backoffice-ui-boui.instructions.md†L14-L55】
- For automation-heavy updates (commands, external builds), replicate the process-driven structure used in `BuildExternalDependenciesConsole` and surface options/flags for idempotency.【F:src/Console/BuildExternalDependenciesConsole.php†L15-L70】


## Estándares de documentación

### 1) PHPDoc (archivo, clase, constantes, propiedades, métodos)

- Describir **qué hace**, **por qué**, y **cómo se usa**.
- Incluir `@package`, `@param`, `@return`, `@throws`, `@template` cuando aplique.
- Documentar **complejidad, side-effects** y **posibles riesgos** (p. ej., N+1 queries).
- Ejemplo rápido:

```php
<?php
/**
 * Query class for retrieving early access movies.
 *
 * Provides endpoint for fetching movies that are available for early access,
 * with authentication required via Sanctum middleware.
 *
 * @package Anystream\Movies\Queries
 */

namespace App\Domains\Anystream\Movies\Queries;

use App\Domains\Anystream\V1\Resources\MovieShortResource;
use App\Domains\Core\Queries\BaseQuery;
use App\Exceptions\AnystreamServerException;
use App\Models\MovieContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Uxmal\Backend\Attributes\RegisterQuery;

#[RegisterQuery('/v1/anystream/movies/early-access', name: 'qry.anystream.movies.early-access.v1', middleware: ['auth:sanctum'])]
class EarlyAccess extends BaseQuery
{
    /** Query name constant for tracking/logging purposes. */
    const QUERY_NAME = 'qry.anystream.movies.early-access.v1';

    /** Resource class used for data transformation. */
    const RESOURCE = MovieShortResource::class;

    /** Metadata for consistent pagination responses. */
    const META = [
        'collection' => 'Early Access',
        'type' => 'movie-short',
        'description' => 'Fetches a list of movies available for early access to authenticated users.',
    ];

    /**
     * Retrieve early access movies for authenticated users.
     *
     * Fetches a curated list of movies available for early access,
     * including metadata like ratings, covers, and streaming links.
     * Uses eager loading to prevent N+1 query issues.
     *
     * @param  Request  $request  The HTTP request instance
     * @return JsonResponse Collection of early access movies
     *
     * @throws AnystreamServerException When movie fetching fails
     */
    #[OA\Get(
        path: '/v1/anystream/movies/early-access',
        description: 'Retrieves a list of movies available for early access to authenticated users',
        summary: 'Get early access movies',
        security: [['sanctum' => []]],
        tags: ['Movies', 'Early Access'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/PageParameter'),
            new OA\Parameter(ref: '#/components/parameters/PerPageParameter'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of early access movies',
                content: new OA\JsonContent(ref: '#/components/schemas/MovieShortReel')
            ),
            new OA\Response(
                response: 500,
                description: 'Server error while fetching content',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->setQuery(MovieContent::query()
                ->select(['id', 'rating', 'title', 'published_date', 'multimedia_group_id'])
                ->where('published', 'yes')
                ->whereHas('multimediaGroup', fn ($q) => $q->where('type', 'moviecontent'))
                ->with([
                    'multimediaGroup:id,uuid',
                    'images' => fn ($q) => $q->select(['id', 'type', 'type_id', 'category_type', 'default', 'url'])
                        ->where('type', 'movies')
                        ->whereIn('category_type', ['poster', 'backdrop'])
                        ->where('default', 'yes'),
                    'translations' => fn ($q) => $q->select(['id', 'foreignkey_id', 'locale', 'field', 'content'])
                        ->where('locale', 'spa')
                        ->where('field', 'title'),
                    'movieGenres:id,name',
                ])
                ->orderByDesc('published_date')
            );

            return response()->json($this->buildResponse());

        } catch (AnystreamServerException $e) {
            throw new AnystreamServerException($e->getMessage(), $e->getCode());
        }
    }
}
```

### 2) OpenAPI con OpenApi\Attributes

**Schemas centralizados**: Usar SOLO `app/Domains/Anystream/V1/Schemas/` para definir `#[OA\Schema]`.

**Referencias**: Usar `ref: '#/components/schemas/...'` para referenciar esquemas.

**Ejemplos**: Incluir `example` y `description` en propiedades scalar y objetos.

**Esquemas reusables**: Definir paginación estándar, errores y metadatos como componentes reutilizables.

---

## Resources y transformación de datos

### Interface obligatoria

Todos los Resources DEBEN implementar `ResourceInterface`:

```php
interface ResourceInterface
{
    public static function fromModel(Model $model, string $lang = 'es'): static;
    public function toArray(): array;
}
```
