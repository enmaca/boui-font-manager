# GitHub Copilot — Repository Custom Instructions (boui-font-manager)

> **Path recommendation:** Place this file at `.github/copilot-instructions.md` so Copilot Chat and Copilot Agents automatically attach it to chats and tasks.
> **Last updated:** 2025‑09‑24

These instructions tell GitHub Copilot how to understand, build, and extend this Laravel package that powers typography management inside the **uxmaltech Backoffice UI** ecosystem.

---

## 1) Project context (what Copilot should know first)

- **Goal:** Manage typography assets (catalogs, categories, versioning) for Backoffice UI pages.
- **Stack:** PHP 8.2+/Laravel 11, Livewire 3, Symfony/Console, Guzzle, DOMPDF font utilities, and uxmaltech packages (`core`, `backoffice-ui`, `backend-cbq`).
- **Front‑end:** Event‑driven JS modules under `resources/js` that run on the global **`boui`** runtime from `uxmaltech/backoffice-ui` and call the backend through **CBQ**.
- **Key entry points:**
  - **Service Provider:** `src/FontManagerServiceProvider.php` (routes, assets, migrations, CQRS discovery, console commands).
  - **Domains (CQRS):** `src/Domains/*` scanned by `RegisterCmdQry` (queries extend `BaseQueryV1`).
  - **Controllers:** `src/Controllers/*` (single‑action). Use `MasterLayout` + fluent Backoffice UI builders.
  - **UI builders:** `src/UI/*` implementing `ContentInterface::getMainContent()` to return arrays of fluent builders (cards, grids, modals, actions).
  - **Routes:** `routes/web.php` (namespace **`enmaca.font-manager.*`**).
  - **JS modules:** `resources/js/*.js` (listen/emit Boui events, refresh components via `boui.get(...).reload()`).
  - **Console:** `src/Console/*` (e.g., `boui-font-manager:build-external-dependencies` to clone/build external font editors and publish artifacts).

---

## 2) Build & run (how to validate changes)

- **Dev loop (Docker + ephemeral Laravel app):**
  1) `make start-dev-env` — build image, scaffold disposable app, link package, start Laravel & Vite.
  2) `make up` / `make down` — start/stop services during iteration.
  3) `make clean-dev-env` — reset the ephemeral app.
- **Required env:** set `GITHUB_TOKEN` (composer access) and `GOOGLE_FONTS_API_KEY` (fonts sync tooling).
- **Assets:** JS bundles under `resources/js/app-assets` are published by the service provider (`public` tag). Console commands may write build artifacts to `public/font-edit`.

> **Definition of Done for Copilot changes**
> - App boots without errors, UI loads, and relevant Boui events fire.
> - Any new page registers its Vite assets and renders via Backoffice UI builders.
> - CQRS endpoints expose OpenAPI metadata and return typed resources.
> - All commands are idempotent and log clear progress/errors.

---

## 3) Coding rules Copilot must follow

### 3.1 Controllers & layouts
- Use **single‑action controllers** and extend the host app’s base controller.
- Compose pages with **Backoffice UI fluent builders** (`UI::`, `Html::`, `Badge::`, `Icon::`, GridJS, modals, actions). **Avoid raw Blade/HTML** unless a builder is missing.
- Always **inject `MasterLayout`**, register required assets (e.g., `viteAsset('...')`), and return rendered HTML.

### 3.2 UI composition layer
- Implement `ContentInterface::getMainContent()` to return the **array of builders**. Keep composition declarative.
- Prefer helper classes and enums; don’t hand‑wire data attributes that Backoffice UI already provides.

### 3.3 CQRS (queries/commands)
- New endpoints must live under `src/Domains/...` and extend the base classes (e.g., `BaseQueryV1`).
- Register via attributes so `RegisterCmdQry` can auto‑discover and expose **OpenAPI** metadata.
- Use eager loading to avoid N+1 queries and return typed resources.

### 3.4 Front‑end integration
- JS modules **listen/emit Boui events** and call backend via **CBQ**. Example patterns live in `resources/js/catalog.js`.
- Refresh components with `boui.get(<id>).reload()`; avoid manual DOM patching.

### 3.5 Routing
- Define HTTP entry points in `routes/web.php` using single‑action controllers.
- **Namespacing:** `enmaca.font-manager.*` for route names (e.g., `enmaca.font-manager.catalog`).

### 3.6 Console tooling
- Follow the `Symfony\Console` pattern: signature, description, clear `handle()` orchestration, idempotent steps, and structured logging.

---

## 4) Documentation, OpenAPI & Resources

### 4.1 PHPDoc (file/class/constants/properties/methods)
- Explain **what**, **why**, and **how to use**; include `@package`, `@param`, `@return`, `@throws`, `@template` when applicable.
- Document **complexity**, **side‑effects**, and **risks** (e.g., N+1 queries, long‑running tasks).

### 4.2 OpenAPI with `OpenApi\Attributes`
- Keep schemas **centralized** in `src/Domains/{Domain}/V1/Schemas/` using `#[OA\Schema]`.
- Reference schemas with `ref: '#/components/schemas/...'` and include **examples** & **descriptions** on scalars and objects.
- Reuse components for **pagination**, **errors**, and **metadata**.

### 4.3 Resources & data transformation (required interface)
```php
interface ResourceInterface
{
    public static function fromModel(Model $model, string $lang = 'es'): static;
    public function toArray(): array;
}
```
- Return **immutable** arrays ready for JSON serialization. Prefer locale‑aware fields via `fromModel($model, $lang)`.

---

## 5) How to prompt Copilot effectively (do this)

- **Start general, then get specific:** Give a short goal, then list precise changes.
- **Ground the request:** Mention the **target file(s)/method(s)** and the **layer** (service provider, domain, controller, UI builder, JS module).
- **Provide examples & acceptance criteria:** e.g., “Add a modal that dispatches a Boui event on confirm; page must register `viteAsset(...)`; include OpenAPI docs for the new query.”
- **Avoid ambiguity:** Specify names (route, event, builder IDs), constraints, and error handling.
- **Keep context tight:** Share only the relevant snippet or path and remove unrelated noise.

> **Shortcut:** Include these helper guides when chatting with Copilot:
> - `.github/instructions/backoffice-ui-php.instructions.md`
> - `.github/instructions/backoffice-ui-boui.instructions.md`

---

## 6) Guardrails & quality checks

- Prefer existing builders/helpers over custom markup or ad‑hoc JS.
- JS changes must align with patterns in `resources/js/catalog.js`; add Boui log calls when debugging.
- New console tasks should be **re‑runnable** and emit structured logs.
- Security & privacy: never hard‑code secrets; respect `.env`; avoid leaking tokens or PII in logs.
- PRs must include: runnable steps, screenshots (if UI), and API examples or OpenAPI changes.

---

## 7) Quick checklist (copy‑paste into PR description)

- [ ] Build: `make start-dev-env` succeeds; feature works end‑to‑end.
- [ ] UI: Uses Backoffice UI builders, assets registered, Boui events emitted/listened.
- [ ] API: Query/command documented with OpenAPI; no N+1; returns typed resources.
- [ ] Tests/Manual: Steps to validate included; console commands idempotent.
- [ ] Docs: PHPDoc updated; README/usage notes added if needed.

---

## 8) Appendix: common routes (examples)

```php
// Keep route names under enmaca.font-manager.*
Route::get('/font-manager/catalog', TypographyCatalogController::class.'@index')
    ->name('enmaca.font-manager.catalog');
Route::get('/font-manager/category', TypographyCategoryController::class.'@index')
    ->name('enmaca.font-manager.category');
```

```js
// Boui event refresh example
boui.on('fonts:catalog:changed', () => boui.get('fonts-catalog-grid').reload());
```

---

### Notes for maintainers
- If this file is **not** located at `.github/copilot-instructions.md`, move it there to ensure automatic attachment in Copilot Chat and Copilot Agents.
- Keep this document concise and updated—Copilot uses it as live context.
