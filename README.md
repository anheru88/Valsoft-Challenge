# Librarium

A library management system: catalogue, membership and circulation. Built from
the specification in [`docs/`](docs), which is the authoritative description of
the product and is kept in step with the code.

```
docs/       PRDs, architecture RFC, API specification — the source of truth
backend/    Laravel 12 REST API                        — complete
frontend/   Angular 20 SPA                             — views built, not yet wired to the API
```

## Running it

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

**Frontend — the views exist, the wiring does not.** All sixteen screens are
built and render, but nothing calls the API yet: every page runs on demo data
and each integration point is marked `// TODO API:`. Sign-in is the visible
consequence — `LoginPage.submit()` validates and stops, so no session starts and
the route guard returns you to the login screen. The Storybook catalogue is the
useful way to review the interface today.

Next: an `AuthApiService`, which alone makes the application navigable, then one
API service and store per feature (RFC §15.2).

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
