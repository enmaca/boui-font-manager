# boui-font-manager — Dev Environment via Makefile

This repository provides a **Makefile** to bootstrap and manage a throwaway Laravel app for developing and testing the **boui-font-manager** package.

---

## 🚧 Prerequisites

- Docker installed and running
- Docker Compose v2 (`docker compose version` available)
- GNU Make
- Environment variables (either exported in your shell or defined in a `.env` at repo root):
  - `GITHUB_TOKEN` — Personal Access Token (for private dependencies / GitHub API)
  - `GOOGLE_FONTS_API_KEY` — Google Fonts Web API key

> You may safely keep these in a local `.env`. The Makefile avoids printing secrets in logs.

---

## 🔧 Configurable variables

You can override these on the fly: `make VAR=value <target>`

| Variable        | Default                         | Description                                     |
|-----------------|----------------------------------|-------------------------------------------------|
| `APP_DIR`       | `devapp`                         | Path where the throwaway Laravel app lives      |
| `DOCKER_IMAGE`  | `boui-font-manager-dev:latest`   | Image name used for dev                         |
| `PKG_PATH`      | `$(PWD)`                         | Path to this package on your machine            |

Absolute paths are computed internally (`PKG_PATH_ABS`, `DEVAPP_ABS`).

---

## 🧰 Main targets (Makefile)

### 1) Bootstrap everything
```bash
make start-dev-env
```
Performs: `check-prereqs` → `build-image` → `devapp` → `skeleton-copy` → `composer-link` → `init-script` → `env-sqlite` → `fix-perms` → `docker-up` → `app-key`.

### 2) Build the image
```bash
make build-image
```
Builds `$(DOCKER_IMAGE)` from the local `Dockerfile`.

### 3) Create Laravel app (if missing)
```bash
make devapp
```
Creates a Laravel 11 app in `APP_DIR` (idempotent).

### 4) Copy skeleton resources
```bash
make skeleton-copy
```
Copies `resources/skeleton/*` over the Laravel app (if present).

### 5) Link package via Composer
```bash
make composer-link
```
- Adds this package as a local path repo to the app.
- Uses `GITHUB_TOKEN` and `GOOGLE_FONTS_API_KEY` (read from your environment or prompted interactively).
- Runs `/app/init-repo.sh` inside the container.

### 6) SQLite env setup
```bash
make env-sqlite
```
Creates `devapp/.env` (if missing) and configures SQLite (`/database/database.sqlite`).

### 7) Fix permissions
```bash
make fix-perms
```
Grants writable perms on `storage/` and `bootstrap/cache`.

### 8) Run the dev container
```bash
make up
```
Runs a privileged, interactive container and starts **both** dev servers:
- **Laravel**: `php artisan serve --host=0.0.0.0 --port=8000` → http://localhost:8000
- **Vite**: `vite --host 0.0.0.0 --port 5173` → http://localhost:5173

> Ports `8000:8000` and `5173:5173` are mapped automatically.

### 9) Stop
```bash
make down
```
There are no persistent containers; stop with `Ctrl+C` in the `make up` terminal or kill the process if needed.

### 10) Generate app key
```bash
make app-key
```
Runs `php artisan key:generate --force` inside the app container.

### 11) Shell into container
```bash
make bash
```
Opens an interactive shell in the dev image, mounting your app and package volumes.

### 12) Clean up
```bash
make clean-dev-env
```
Removes the `devapp/` directory and any transient artifacts. (No persistent containers are defined.)

---

## 🔐 Secrets and environment

- You can define `GITHUB_TOKEN` and `GOOGLE_FONTS_API_KEY` in your host shell or in a repo-level `.env` file.
- The Makefile will **not** echo secret values; it will pass them to the container as environment variables so that:
  - Composer auth is set (`COMPOSER_AUTH` for github oauth)
  - The Google Fonts sync command can run:  
    `php artisan boui-font-manager:update-google-fonts-database --api-key="${GOOGLE_FONTS_API_KEY}"`

---

## 📝 Notes & tips

- The dev app is **ephemeral** and safe to delete with `make clean-dev-env`.
- The Vite dev server is configured to serve from the container paths and to allow the `/app/resources` tree.
- If you see Node/V8 OOM errors during `npm run dev`, the startup script sets `NODE_OPTIONS=--max-old-space-size=4096` to increase the heap. You can adjust it if needed.

---

## 🆘 Troubleshooting

- **Vite allow-list error**: ensure `vite.config.js` has `server.fs.allow` including `/app` and `/app/resources`.
- **Ports already in use**: stop any process using 8000/5173 or change ports in `start.sh` and `vite.config.js`.
- **GitHub 401 on Composer**: verify `GITHUB_TOKEN` scopes and that the token is exported or available in `.env`.
- **Google Fonts fetching fails**: verify `GOOGLE_FONTS_API_KEY` is valid; the command will prompt if not provided.

---

## 📚 Quickstart

```bash
# (optional) in repo root .env
echo "GITHUB_TOKEN=ghp_xxx" >> .env
echo "GOOGLE_FONTS_API_KEY=AIzaSy..." >> .env

# full bootstrap
make start-dev-env

# or step-by-step
make build-image
make devapp
make skeleton-copy
make composer-link
make env-sqlite
make up
```

Happy hacking! 🎉
