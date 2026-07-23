# Running Librarium with Docker

One command builds and serves the whole system — MariaDB, the Laravel API, and
the Angular SPA:

```bash
./run.sh
```

Then open **http://teachassignment.local:8080** — or **http://localhost:8080**,
which always works regardless of the host name (see the WSL2 note below).

## What `./run.sh` does

1. Copies each `.env.example` to `.env` if it does not exist yet
   (root, `backend/`, `frontend/`).
2. Generates a Laravel `APP_KEY` into the root `.env` when it is empty.
3. Maps `teachassignment.local` to `127.0.0.1` in `/etc/hosts` (asks for sudo).
   On WSL2 it also tries the Windows hosts file, since that is the one the
   browser uses.
4. Runs `docker compose up -d --build`.
5. Waits until the API answers on `/up`. Migrations and demo seeding run
   automatically on the first boot.

## Host name and WSL2

`http://localhost:8080` always reaches the app. The `teachassignment.local`
alias only resolves once it is in the hosts file of the machine running the
**browser**.

On WSL2 that machine is Windows, and Windows ignores the Linux `/etc/hosts`. The
Windows hosts file needs administrator rights, so `./run.sh` cannot edit it for
you — add the entry once from an **Administrator** PowerShell:

```powershell
Add-Content -Path "$env:windir\System32\drivers\etc\hosts" -Value "127.0.0.1 teachassignment.local"
```

Or just use `http://localhost:8080` and skip the alias entirely.

Other sub-commands:

```bash
./run.sh logs   # follow container logs
./run.sh down   # stop and remove the containers
```

## The stack

| Service    | Image / build                       | Port (host) | Role |
|------------|-------------------------------------|-------------|------|
| `db`       | `mariadb:11`                        | `3306`      | Database, data on a named volume |
| `backend`  | `backend/Dockerfile` (php 8.4 cli)  | `8000`      | Laravel API (`artisan serve`, 4 workers) |
| `frontend` | `frontend/Dockerfile` (nginx)       | `8080`      | Built SPA + reverse-proxy of `/api` + `/storybook` |

The browser only ever talks to the frontend. nginx serves the static Angular
build and proxies `/api/…` to the backend, so the SPA and API share one origin
and no CORS configuration is needed. This matches the production Angular
environment, whose `apiUrl` is the relative `/api/v1`. The static Storybook of
the shared UI primitives is served alongside it at **`/storybook/`**.

## Configuration

All knobs live in the root `.env` (created from `.env.example`):

- `APP_HOST` / `APP_PORT` — public hostname and port for the app.
- `BACKEND_PORT`, `DB_PORT` — host ports for the API and database.
- `DB_*` — MariaDB database name and credentials.
- `APP_KEY` — filled in by `./run.sh`.

The backend container reads `backend/.env` for its base Laravel config; the
compose file overrides the database connection and a few production settings on
top of it (Laravel's dotenv is immutable, so the container's environment wins).

## Notes

- `artisan serve` with `PHP_CLI_SERVER_WORKERS=4` is used for a simple,
  dependency-light demo server. For a real production deployment swap it for
  php-fpm + nginx or FrankenPHP.
- Demo data is seeded only when the database is empty, so restarts do not
  duplicate it. To start from scratch: `./run.sh down && docker volume rm valsoft_db-data`.
