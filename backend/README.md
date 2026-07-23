# Librarium — Backend

REST API for the library management system specified in [`../docs`](../docs):
the PRDs, RFC-001 and the API specification are the source of truth; this
service implements them.

## Stack

Laravel 12 · PHP 8.2+ · MariaDB · Sanctum token auth · spatie/laravel-permission
· Pest · Pint · PHPStan (larastan) level 8.

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

`.env.example` points at MariaDB, the production engine. For a zero-setup local
run, set `DB_CONNECTION=sqlite` and `touch database/database.sqlite` — the
migrations apply MariaDB-only features (FULLTEXT, CHECK constraints) behind
driver checks, so the schema builds on both.

The demo seeder builds a small library with a year of history behind it: 81
titles by 57 authors across 8 categories, 38 accounts and ~340 loans spread over
twelve months, including open, overdue and returned ones. That shape is
deliberate — the dashboard's monthly series, the popular-author ranking and the
overdue report all need movement to say anything.

Accounts, all with the password `password`:

| Role | Email |
|---|---|
| Administrator | `admin@librarium.test` |
| Librarian | `librarian@librarium.test` (and `librarian2@librarium.test`) |
| Member | `member@librarium.test` (plus 34 more, `nombre.apellido@example.com`) |

Seeded data obeys the same rules the API enforces: `available_copies` equals
total minus open loans, no member holds more than the active limit, and nobody
holds the same title twice at once. Re-running `migrate:fresh --seed` produces a
different but equally valid library.

## Commands

```bash
vendor/bin/pest                  # test suite
vendor/bin/pest --filter=Loans   # one directory or test name
composer lint                    # Pint (PSR-12)
composer analyse                 # PHPStan level 8
composer openapi                 # regenerate openapi.json
```

The suite runs on SQLite by default. To exercise the MariaDB-only guards —
`SELECT ... FOR UPDATE` on check-out, FULLTEXT relevance search — point
`phpunit.xml` at a MariaDB `librarium_test` database; tests that need them skip
themselves elsewhere rather than pretending to pass.

## Layout

Business logic lives under `app/Library`, not in Laravel's default folders
(RFC-001 §4). `app/Http` is a thin edge: a controller authorizes, builds a DTO,
calls an action and returns a resource.

```
app/Library/
├── Shared/            # DomainException, DomainEvent, Clock, TransactionRunner,
│                      #   pagination and sorting value objects, base repository
└── Domains/
    ├── Auth/          # login, registration, password change, token issuing
    ├── Users/         # accounts, roles, the permission matrix
    ├── Books/         # catalogue, ISBN value object, search implementations
    ├── Authors/
    ├── Categories/
    ├── Loans/         # circulation: check-out, check-in, the BR-LOAN rules
    └── Dashboard/     # aggregate reporting reads
```

Each domain owns its models, contracts, repositories, actions, DTOs, policies,
events, requests, resources and exceptions. Domains talk to each other through
contracts only — circulation asks Books to adjust its counter rather than
writing the column itself.

## API

Everything is under `/api/v1` (ADR-8). All routes require a bearer token except
`POST /auth/register` and `POST /auth/login`.

Errors share one envelope:

```json
{ "error": { "code": "LOAN_LIMIT_REACHED",
             "message": "This member already has 5 active loans (limit 5).",
             "details": { "limit": 5, "active_loans": 5 },
             "trace_id": "8c9f1e2a-…" } }
```

`422` means the input is malformed; `409` means the input is fine and the world
disagrees. The full code registry is in the API specification §10. Every
response carries `X-Request-Id`, honouring an inbound one, so a user report maps
to its logs.

`php artisan route:list --path=api` prints the current surface.

## OpenAPI

The specification is generated from the code by
[Scramble](https://scramble.dedoc.co/): FormRequests become request bodies, API
Resources become response schemas, so it cannot drift the way a hand-written
document does. What inference cannot see is declared explicitly — the error
envelope via a document transformer, and each endpoint's business refusals via
`#[DomainErrors([...])]` on the controller method.

- `/docs/api` — interactive documentation. Open to everyone in local; elsewhere
  it needs an authenticated administrator.
- `/docs/api.json` — the live document.
- `openapi.json` — the committed contract. `composer openapi` regenerates it,
  and a test fails if it drifts from the code, which is the breaking-change
  detection RFC-001 §13 asks for.

## Authorization

Roles and capabilities are data, not code (ADR-11). Policies ask for a
capability — `$actor->can('catalog.manage')` — and which role carries which
capability lives in the `spatie/laravel-permission` tables, seeded by a
migration from `App\Library\Domains\Users\Enums\Permission`. Composing a new
role is an insert; no policy changes.

A user carries exactly one role: writes take a single `role`, reads return
`roles[]` plus the effective `permissions[]`, which lets a client hide actions
it cannot perform. Role changes and deactivation revoke every token the account
holds, so privileges cannot outlive the decision.

## Configuration

`config/library.php` holds the tunable business constants — active loan limit,
loan window, pagination caps, minimum search length — so the rules read them
instead of hard-coding numbers.
