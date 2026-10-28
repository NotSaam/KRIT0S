#!/bin/sh
set -e

# Only the web container runs migrations; workers just wait for the schema.
if [ "${KRIT0S_ROLE:-web}" = "web" ]; then
    php artisan migrate --force

    # Public demo: seed fictitious data the first time the database is empty.
    if [ "${APP_DEMO:-false}" = "true" ] && [ "$(php artisan tinker --execute='echo App\\Models\\User::count();')" = "0" ]; then
        php artisan db:seed --force
    fi

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
