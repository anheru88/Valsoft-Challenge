#!/bin/sh
# Backend container start-up: wait for the database, bring the schema up to
# date, seed the demo data once, then serve the API.
set -e
cd /var/www/html

echo "[entrypoint] waiting for database at ${DB_HOST}:${DB_PORT:-3306} ..."
until php -r '
    try {
        new PDO(
            sprintf("mysql:host=%s;port=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "3306"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
    } catch (Throwable $e) {
        exit(1);
    }
'; do
    sleep 2
done
echo "[entrypoint] database is up."

# A key must exist. Compose normally supplies APP_KEY (./run.sh fills it in);
# fall back to an ephemeral one held only in this process's environment, since
# there is no .env file inside the image to write to.
if [ -z "${APP_KEY}" ]; then
    echo "[entrypoint] APP_KEY empty — generating an ephemeral key."
    APP_KEY="$(php artisan key:generate --show)"
    export APP_KEY
fi

php artisan migrate --force

# Seed only when the library is empty, so restarts don't duplicate demo data.
USERS="$(php artisan tinker --execute="echo DB::table('users')->count();" 2>/dev/null | tail -n 1 | tr -dc '0-9')"
if [ -z "${USERS}" ] || [ "${USERS}" = "0" ]; then
    echo "[entrypoint] empty database — seeding demo data."
    php artisan db:seed --force
else
    echo "[entrypoint] ${USERS} users present — skipping seed."
fi

echo "[entrypoint] serving API on :8000"
# --no-reload lets artisan honour PHP_CLI_SERVER_WORKERS, so the built-in
# server handles requests concurrently instead of one at a time.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
