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

## Additional resources for contributors
- Use the Makefile quickstart commands in the README for local setup and troubleshooting tips (ports, Vite allow-list, credentials).【F:README.md†L34-L139】
- Explore the uxmaltech Backoffice UI package (linked in composer repositories) for additional fluent builders and examples when extending UI modules.【F:composer.json†L46-L69】
