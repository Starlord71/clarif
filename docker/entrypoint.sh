#!/bin/sh
set -e

# Wait until PostgreSQL accepts connections before doing anything else. The
# healthcheck in docker compose already gates on this, but the loop keeps the
# image usable on its own (for example, against a database that is slower to
# become ready).
until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-clarif}" -q; do
    echo "Waiting for PostgreSQL at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
    sleep 2
done

echo "PostgreSQL is ready."

# Migrations run only in the service that owns them (the app container). The
# queue worker sets RUN_MIGRATIONS=false and waits for the app to be healthy,
# which avoids two containers migrating the same database at the same time.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# exec replaces the shell so the container forwards signals to the given
# command. This is what lets the same image serve as both web and worker.
exec "$@"
