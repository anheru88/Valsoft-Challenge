# Librarium

A library management system: catalogue, membership and circulation. Built from
the specification in [`docs/`](docs), which is the authoritative description of
the product and is kept in step with the code.

```
docs/       PRDs, architecture RFC, API specification — the source of truth
backend/    Laravel 12 REST API                        — complete
frontend/   Angular 20 SPA                             — complete, reading and writing the API
```

## Running it with Docker

One command builds and serves the whole system — MariaDB, the Laravel API and the
Angular SPA — behind a single port:

```bash
./run.sh
```

Then open **http://teachassignment.local:8080** and sign in with a seeded account
(below). `./run.sh logs` follows the containers; `./run.sh down` stops them.

What the script does, in order:

1. copies each `.env.example` to `.env` (root, `backend/`, `frontend/`) if one is
   missing;
2. generates a Laravel `APP_KEY` into the root `.env` when it is empty;
3. maps `teachassignment.local` to `127.0.0.1` in `/etc/hosts` (asks for sudo);
4. runs `docker compose up -d --build`;
5. waits until the API answers on `/up` — the schema migration and the demo seed
   run automatically on the first boot, and are skipped on later restarts.

Three containers behind one public door. The browser only ever talks to the
frontend: nginx serves the built SPA, proxies `/api/…` to the backend (same
origin, so no CORS) and serves the static Storybook at `/storybook/`.

| Service | Build | Host port | Role |
|---|---|---|---|
| `db` | `mariadb:11` | `3306` | Database; data on a named volume |
| `backend` | `backend/Dockerfile` | `8000` | Laravel API (`artisan serve`, 4 workers) |
| `frontend` | `frontend/Dockerfile` | `8080` | Built SPA + proxy of `/api` + `/storybook` |

```
frontend:8080
  ├ /            the SPA
  ├ /api/v1/…    → backend:8000 → db
  └ /storybook/  the component catalogue (static)
```

Configuration lives in the root `.env` (public host, ports, database credentials,
`APP_KEY`). If ports `8080`, `8000` or `3306` are already taken on your machine —
for instance by the local dev servers below — change `APP_PORT`, `BACKEND_PORT` or
`DB_PORT` there and re-run. [`DOCKER.md`](DOCKER.md) has the full reference.

> **`http://localhost:8080` always works**, whatever the host name. The
> `teachassignment.local` alias is a convenience that only resolves once it is in
> your hosts file. On **WSL2** the browser runs on Windows, which ignores the
> Linux `/etc/hosts` the script edits — so use `localhost`, or add the alias to
> the Windows hosts file from an *Administrator* PowerShell:
> ```powershell
> Add-Content -Path "$env:windir\System32\drivers\etc\hosts" -Value "127.0.0.1 teachassignment.local"
> ```

> The `/storybook/` sub-path is a property of the Docker build alone: it is nginx
> serving the pre-built static Storybook. The Angular dev server (`ng serve`, on
> `:4200` or whatever `--port` you pass) has no such route, so a `/storybook` path
> on the dev server will not resolve — under local development Storybook is its own
> server (`npm run storybook`, `:6006`).

## Running it locally

Two servers, two terminals. Both default to a local SQLite database and need no
external services.

```bash
# API — http://localhost:8000
cd backend
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite          # .env.example defaults to SQLite
php artisan migrate --seed
php artisan serve

# SPA — http://localhost:4200
cd frontend
npm install
npm start                                # add --port 4300 if 4200 is taken
```

`ng serve` proxies `/api` to `http://localhost:8000`, so the two talk to each
other without CORS configuration.

Seeded accounts, all with the password `password`:

| Role | Email |
|---|---|
| Administrator | `admin@librarium.test` |
| Librarian | `librarian@librarium.test` |
| Member | `member@librarium.test` |

The seed builds a small library with a year of history behind it: 81 titles by
57 authors, 38 accounts and ~340 loans spread across twelve months, including
open, overdue and returned ones. That shape is deliberate — the dashboard's
monthly series, the popular-author ranking and the overdue report all need
movement before they say anything.

## Where things stand

**Backend — complete.** Every endpoint in the API specification is implemented
and tested: authentication, users and roles, catalogue, circulation, search,
dashboard and reports. 157 tests, PHPStan level 8, Pint clean.

**Frontend — complete.** All screens are built and wired to the API — sign-in and
session, catalogue (books, authors, categories), circulation, search, the
dashboard and reports, and the profile and change-password screens. Every feature
has its own API service and signal store; no demo-data stubs remain.

**Deployment — complete.** The whole stack runs from `./run.sh` (see above).

## What's left

Everything below is additive and lives in this repository: the modular-monolith
seams — Actions, Contracts, `BookSearchInterface`, the Laravel scheduler — are
exactly where these features plug in, so none of it needs a rewrite.

**Operational — documented but not yet built.** The nightly reconciliation of
`books.available_copies` is described as a production job (RFC-001) and performed
inline by the seeder, but there is no Artisan command or scheduler entry for it
yet. Next: a `books:reconcile-availability` command on Laravel's scheduler (cron).

**Search at scale — Meilisearch.** DB-native FULLTEXT (MariaDB) and LIKE (SQLite)
implement `BookSearchInterface` today; the documented trigger to move on is a
catalogue that outgrows FULLTEXT. Adding a `MeilisearchBookSearch` plus a compose
service is the whole change — the rest of the app already depends only on the
interface. Redis caching is a second deferred seam (prepared, dependency absent).

**Shelf location.** Books carry no physical location. A `location` / call-number
field would let staff and members see where a copy actually sits — the librarian
pain point the PRD names outright ("no way to know if a copy is available without
walking to the shelf"). A migration, a Resource field and one catalogue column.

**AI — the next step.** An **MCP server inside Laravel** that exposes the domain
as tools — search the catalogue, check availability, place or return a loan, list
overdue — by reusing the existing Actions and Contracts rather than duplicating
logic. Agents, and in time Librarium's own UI, consume those tools. On top of
that, **scheduled (cron) agents** on Laravel's scheduler for the recurring work:
the reconciliation above, overdue notices, popularity recompute.

## Working on it

```bash
# backend
vendor/bin/pest            # tests
composer lint              # Pint
composer analyse           # PHPStan level 8
composer openapi           # regenerate openapi.json after changing a route

# frontend
npm run build
npm run storybook          # component catalogue on :6006
npm run stories:check      # opens every story in a real browser, fails if any breaks
```

Local development and the test suite run on SQLite; MariaDB is the production
target. Migrations guard the MariaDB-only features — FULLTEXT indexes and CHECK
constraints — behind driver checks, and the tests that need them skip themselves
rather than pretending to pass.

## Documentation

| Document | What it holds |
|---|---|
| [`docs/01-prd-backend.md`](docs/01-prd-backend.md) | Personas, `FR-*` requirements, NFRs, `BR-*` business rules, acceptance criteria |
| [`docs/02-prd-frontend.md`](docs/02-prd-frontend.md) | Routes, layouts, UX states, accessibility, Storybook organization |
| [`docs/03-architecture-rfc.md`](docs/03-architecture-rfc.md) | RFC-001: every decision with rationale, alternatives and trade-offs; ER model; ADR log |
| [`docs/04-api-specification.md`](docs/04-api-specification.md) | The REST contract: endpoints, envelopes, status codes, error registry |

`backend/openapi.json` is the machine-readable twin, generated from the code by
Scramble and committed. A test fails if it drifts, which is the breaking-change
detection RFC-001 §13 asks for.

When code and documentation disagree, one of them is wrong: fix the code, or
amend the document with rationale the way the RFC's own ADRs do. ADR-11, which
replaced the role enum with permission tables, is the worked example.

## Architecture in one paragraph

The backend is a modular monolith with DDD-Lite: business logic lives under
`app/Library/Domains/<Domain>`, `app/Http` is a thin edge, and domains talk to
each other through contracts rather than by reaching into each other's tables.
Authorization is expressed as capabilities held in data, so a new role is
configuration rather than a deployment. The frontend mirrors that shape with a
feature-based tree and decides what to show from the capabilities the API
reports, never from a role name.
