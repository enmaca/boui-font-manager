#!/usr/bin/env sh
cd /app || exit 1

#
# php artisan octane:frankenphp --host=0.0.0.0 --port=80 --workers=1 -vvv --watch

export NODE_OPTIONS="${NODE_OPTIONS:---max-old-space-size=4096}"

npm run dev -- --host 0.0.0.0 --port 5173 & exec php artisan serve --host=0.0.0.0 --port=8000
