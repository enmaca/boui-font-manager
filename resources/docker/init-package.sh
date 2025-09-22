#!/usr/bin/env bash
set -euo pipefail

# This script runs inside the composer container with /app as working dir
# Required envs: GITHUB_TOKEN, PKG_NAME, COMPOSER_AUTH

# Ensure composer sees the token for HTTPS git operations
if [ -n "${COMPOSER_AUTH:-}" ]; then
  echo "Using COMPOSER_AUTH for GitHub OAuth"
else
  export COMPOSER_AUTH="{\"github-oauth\":{\"github.com\":\"${GITHUB_TOKEN:-}\"}}"
fi

mkdir -p .cache/composer && chmod -R 777 .cache/composer || true

# Normalize composer settings
composer config minimum-stability dev || true
composer config prefer-stable true || true

# Link local package via path repo
composer config repositories.local '{"type":"path","url":"/pkg","options":{"symlink":true}}'

# Add required VCS repos (HTTPS URLs to work with token)
composer config repositories.uxmaltech-core '{"type":"vcs","url":"https://github.com/uxmaltech/core.git"}'
composer config repositories.backend-cbq '{"type":"vcs","url":"https://github.com/uxmaltech/backend-cbq.git"}'
composer config repositories.backoffice-ui '{"type":"vcs","url":"https://github.com/uxmaltech/backoffice-ui.git"}'

# Require the package under development
composer require "enmaca/boui-font-manager" "laravel/octane" --prefer-source --no-interaction --no-cache --prefer-dist --optimize-autoloader

php artisan octane:install --server=frankenphp

php artisan vendor:publish --provider="Uxmal\Backoffice\UxmalBackofficeUIServiceProvider" --tag=config
php artisan vendor:publish --provider="Uxmal\Backoffice\UxmalBackofficeUIServiceProvider" --tag=public
php artisan vendor:publish --provider="Enmaca\Backoffice\FontManager\FontManagerServiceProvider" --tag=public

npm cache clean --force
mkdir -p /tmp/empty-cache

npm install --cache /tmp/empty-cache
npm install --cache /tmp/empty-cache --prefix vendor/uxmaltech/backoffice-ui
npm run build

php artisan migrate

if [[ -n "${GOOGLE_FONTS_API_KEY:-}" ]]; then
  echo "Running: php artisan boui-font-manager:update-google-fonts-database --api-key=****(hidden)"
  php artisan boui-font-manager:update-google-fonts-database --api-key="${GOOGLE_FONTS_API_KEY}"
else
  echo "Skipping Google Fonts database update, no API key provided"
fi

php artisan boui-font-manager:build-external-dependencies


