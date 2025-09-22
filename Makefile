# Makefile — Dev env bootstrap for a Laravel package (boui-font-manager)
# Usage:
#   make start-dev-env            # one-shot: create app, link this package, build & run docker
#   make up / make down           # start/stop containers
#   make logs / make bash         # follow logs / shell into the app container
#   make clean-dev-env            # stop and remove the throwaway app and its containers

# ---- Configurable variables (override with: make VAR=value) ------------------
APP_DIR            ?= devapp
DOCKER_IMAGE      ?= boui-font-manager-dev:latest
PKG_PATH           ?= $(PWD)                        # path to this package on your machine
PKG_PATH_ABS      := $(abspath $(PKG_PATH))
DEVAPP_ABS        := $(abspath $(APP_DIR))
DC                 := docker compose -f ./docker-compose.yml

# Composer cache directory on host (override with: make COMPOSER_CACHE=...)
COMPOSER_CACHE     ?= $(HOME)/.cache/composer

# Carga ./.env si existe (variables disponibles en Make)
ifneq (,$(wildcard ./.env))
	include ./.env
	export
endif

# ----------------------------------------------------------------------------
.PHONY: start-dev-env devapp build-image composer-link init-script env-sqlite fix-perms \
        docker-up up down logs bash app-key clean-dev-env check-prereqs

start-dev-env: check-prereqs build-image devapp skeleton-copy composer-link init-script env-sqlite fix-perms docker-up app-key ## Bootstrap everything
	@echo "✅ Dev environment is ready: http://localhost:8080"

# ---- Safety checks ----------------------------------------------------------
check-prereqs:
	@command -v docker >/dev/null 2>&1 || { echo "❌ docker is required"; exit 1; }
	@docker version >/dev/null 2>&1 || { echo "❌ docker engine not running"; exit 1; }
	@docker compose version >/dev/null 2>&1 || { echo "❌ docker compose v2 is required"; exit 1; }

build-image:
	@echo "🐳 Building $(DOCKER_IMAGE) from ./Dockerfile"
	@docker build -t $(DOCKER_IMAGE) -f ./Dockerfile .

# ---- Create a fresh Laravel app (throwaway for package dev) -----------------
devapp:
	@if [ ! -d "$(APP_DIR)" ] || [ ! -f "$(APP_DIR)/artisan" ]; then \
	  echo "📦 Creating Laravel app in $(APP_DIR)"; \
	  mkdir -p $(APP_DIR); \
	  mkdir -p $(COMPOSER_CACHE); \
	  docker run --rm -u $$(id -u):$$(id -g) \
	    -e COMPOSER_CACHE_DIR="/tmp/composer-cache" \
	    -v "$(COMPOSER_CACHE)":/tmp/composer-cache \
	    -v $(PWD)/$(APP_DIR):/app -w /app $(DOCKER_IMAGE) \
	    sh -lc 'composer create-project laravel/laravel:^11.0 . && composer config minimum-stability dev && composer config prefer-stable true'; \
	else \
	  echo "ℹ️  Laravel app already exists at $(APP_DIR)"; \
	fi

# REPLACE resource/skeleton/app/* => $(APP_DIR)/app/* IF YOU WANT TO OVERRIDE THE DEFAULT LARAVEL APP
# ---- Copy skeleton over app directory ----------------------------
skeleton-copy: devapp
	@echo "📁 Copying skeleton app resources"
	@if [ -d "$(PKG_PATH_ABS)/resources/skeleton" ]; then \
		cp -R "$(PKG_PATH_ABS)/resources/skeleton/." "$(APP_DIR)/"; \
		echo "   ✅ Copied $(PKG_PATH_ABS)/resources/skeleton/* -> $(APP_DIR)/"; \
	else \
		echo "   ⚠️  No skeleton at $(PKG_PATH_ABS)/resources/skeleton (skipping)"; \
	fi

# ---- Wire this package into the app using a local path repository -----------
composer-link: devapp init-script
	@set -e; \
	if [ -z "$(GITHUB_TOKEN)" ]; then \
	  read -p "Enter your GitHub token: " token; \
	  GITHUB_TOKEN="$$token"; \
	else \
	  GITHUB_TOKEN="$(GITHUB_TOKEN)"; \
	fi; \
	if [ -z "$(GOOGLE_FONTS_API_KEY)" ]; then \
	  read -p "Enter your Google Fonts Api Key: " gf_api_key; \
      GOOGLE_FONTS_API_KEY="$$gf_api_key"; \
	else \
	  GOOGLE_FONTS_API_KEY="$(GOOGLE_FONTS_API_KEY)"; \
	fi; \
	echo "🔑 Using GitHub token ****(hidden)"; \
	echo "🔑 Using Google Fonts Api Key ****(hidden)"; \
	mkdir -p $(COMPOSER_CACHE); \
	docker run --rm -u $$(id -u):$$(id -g) \
	  -e GITHUB_TOKEN="$$GITHUB_TOKEN" \
	  -e GOOGLE_FONTS_API_KEY="$$GOOGLE_FONTS_API_KEY" \
	  -e COMPOSER_CACHE_DIR="/tmp/composer-cache" \
	  -v "$(COMPOSER_CACHE)":/tmp/composer-cache \
	  -v "$(PKG_PATH_ABS)":/pkg \
	  -v "$(DEVAPP_ABS)":/app -w /app $(DOCKER_IMAGE) \
	  sh -lc 'export COMPOSER_AUTH="{\"github-oauth\":{\"github.com\":\"$$GITHUB_TOKEN\"}}"; export GOOGLE_FONTS_API_KEY="$$GOOGLE_FONTS_API_KEY"; bash /app/init-package.sh'

# ---- Configure SQLite for a DB-less, fast bootstrap -------------------------
env-sqlite: devapp
	@set -e; \
	if [ ! -f "$(APP_DIR)/.env" ]; then \
	  cp "$(APP_DIR)/.env.example" "$(APP_DIR)/.env"; \
	fi; \
	mkdir -p "$(APP_DIR)/database"; \
	touch "$(APP_DIR)/database/database.sqlite"; \
	# Set SQLite in .env using DEVAPP_ABS path. If keys don't exist, append them.
	sed -i -E 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' "$(APP_DIR)/.env" || true; \
	sed -i -E 's/^DB_HOST=.*/DB_HOST=/' "$(APP_DIR)/.env" || true; \
	sed -i -E 's/^DB_PORT=.*/DB_PORT=/' "$(APP_DIR)/.env" || true; \
	sed -i -E 's/^DB_USERNAME=.*/DB_USERNAME=/' "$(APP_DIR)/.env" || true; \
	sed -i -E 's#^DB_DATABASE=.*#DB_DATABASE=/database/database.sqlite#' "$(APP_DIR)/.env" || true; \
	grep -q '^DB_CONNECTION=' "$(APP_DIR)/.env" || echo 'DB_CONNECTION=sqlite' >> "$(APP_DIR)/.env"; \
	grep -q '^DB_HOST=' "$(APP_DIR)/.env" || echo 'DB_HOST=' >> "$(APP_DIR)/.env"; \
	grep -q '^DB_PORT=' "$(APP_DIR)/.env" || echo 'DB_PORT=' >> "$(APP_DIR)/.env"; \
	grep -q '^DB_USERNAME=' "$(APP_DIR)/.env" || echo 'DB_USERNAME=' >> "$(APP_DIR)/.env"; \
	grep -q '^DB_DATABASE=' "$(APP_DIR)/.env" || echo "DB_DATABASE=/database/database.sqlite" >> "$(APP_DIR)/.env"; \
	echo "✅ .env configured for SQLite at /database/database.sqlite"

# ---- Permissions & up -------------------------------------------------------
fix-perms:
	@chmod -R a+rw $(APP_DIR)/storage $(APP_DIR)/bootstrap/cache || true

app-key:
	@echo "🔐 Generating app key via docker run"
	@docker run --rm -u $$(id -u):$$(id -g) \
		-v "$(DEVAPP_ABS)":/app -w /app \
		$(DOCKER_IMAGE) \
		sh -lc 'php artisan key:generate --force' || true

# ---- Docker lifecycle helpers ----------------------------------------------
docker-up: up
up:
	@set -e; \
	mkdir -p $(COMPOSER_CACHE); \
	echo "🚀 Corriendo contenedor con imagen $(DOCKER_IMAGE), exponiendo puertos 8000 y 5173"; \
	docker run --rm -it --privileged \
	  -u $$(id -u):$$(id -g) \
	  -e GITHUB_TOKEN="$$GITHUB_TOKEN" \
	  -e COMPOSER_CACHE_DIR="/tmp/composer-cache" \
	  -v "$(COMPOSER_CACHE)":/tmp/composer-cache \
	  -v "$(PKG_PATH_ABS)":/pkg \
	  -v "$(DEVAPP_ABS)":/app -w /app \
	  -p 8000:8000 \
	  -p 5173:5173 \
	  $(DOCKER_IMAGE) bash /app/start.sh


down:
	@echo "⚠️ No hay contenedores persistentes definidos; usa kill si necesitas cerrar algo manualmente"

logs:
	@echo "⚠️ Logs desde docker run no se pueden seguir una vez que termine; ejecuta directamente dentro del contenedor si está corriendo"

bash:
	@docker run --rm -it -u $$(id -u):$$(id -g) \
	  -e COMPOSER_CACHE_DIR="/tmp/composer-cache" \
	  -v "$(COMPOSER_CACHE)":/tmp/composer-cache \
	  -v "$(PKG_PATH_ABS)":/pkg \
	  -v "$(DEVAPP_ABS)":/app -w /app \
	  -p 8080:80 \
	  $(DOCKER_IMAGE) bash

# ---- Cleanup ----------------------------------------------------------------
clean-dev-env: down
	@rm -rf $(APP_DIR)
	@echo "🧹 Cleaned $(APP_DIR) and stopped containers"

init-script: devapp
	@echo "📝 Writing /$(APP_DIR)/init-package.sh"
	@cat $(PKG_PATH_ABS)/resources/docker/init-package.sh > $(APP_DIR)/init-package.sh
	@chmod +x $(APP_DIR)/init-package.sh
	@echo "📝 Writing /$(APP_DIR)/start.sh"
	@cat $(PKG_PATH_ABS)/resources/docker/start-dev.sh > $(APP_DIR)/start.sh
	@chmod +x $(APP_DIR)/start.sh
