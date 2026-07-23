# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repository is

**Librarium**, an MVP library management system (Senior Backend Engineer assessment). Two directories:

- `docs/` — the specification, and the authoritative one. The originating brief is `docs/Prompt.md`; the four numbered docs are its output.
- `backend/` — the Laravel 12 API implementing it. See `backend/README.md` for setup; commands are `vendor/bin/pest`, `composer lint`, `composer analyse`, `composer openapi`.
- `frontend/` — the Angular 20 SPA of `docs/02-prd-frontend.md`, reading and writing that API. Commands are `npm start`, `npm run storybook`, `npm run stories:check`.

When code and docs disagree, that is a bug in one of them: fix the code, or amend the doc with rationale the way the RFC's own ADRs do (ADR-11 is the worked example).

| Doc | Role |
|---|---|
| `docs/01-prd-backend.md` | Product requirements: personas, `FR-*` functional requirements, NFRs, `BR-*` business rules, acceptance criteria |
| `docs/02-prd-frontend.md` | Angular app requirements: routes, layouts, UX states, a11y, Storybook |
| `docs/03-architecture-rfc.md` | RFC-001: architecture decisions with rationale/alternatives/trade-offs, ER model, indexes, ADR log |
| `docs/04-api-specification.md` | REST contract: endpoints, headers, envelopes, status codes, error-code registry |

## Cross-document conventions

- Requirements are ID-addressable: `FR-<DOMAIN>-<n>` (functional), `BR-<DOMAIN>-<n>` (business rule), `ADR-<n>` (decision). Reference these IDs instead of restating rules; when adding requirements, keep the numbering scheme and the "every rule is testable" property.
- The permission matrix (`01-prd-backend.md` §8.3) is the single source of truth for authorization. Roles and capabilities live in the `spatie/laravel-permission` tables (ADR-11, which supersedes ADR-3's role enum): policies ask `$actor->can('catalog.manage')`, never for a role name, and `App\Library\Domains\Users\Enums\Permission` maps capabilities to roles. The API spec marks each endpoint 🅰 admin / 🅻 librarian / 🅼 member.
- Every architecture claim in the RFC carries rationale + alternatives + trade-offs. Match that standard in edits; unjustified assertions are out of style here.
- Mermaid is used for flow/sequence/ER diagrams.

## Architecture the docs commit to

**Backend** — Laravel 12 / PHP 8.4 / MariaDB / Sanctum, as a **modular monolith with DDD-Lite**. All business logic lives under `app/Library` (`Shared/`, `Application/`, `Domains/<Domain>/`, `Infrastructure/`); `app/Http` is a thin edge. Domains: Books, Authors, Categories, Users, Loans, Dashboard, Auth — each with its own Models/Services/Actions/Repositories/Contracts/DTOs/ValueObjects/Policies/Events/Requests/Resources/Exceptions. Dependencies point inward; cross-domain access only via Contracts or Events; domain code imports no Laravel facades.

**Frontend** — Angular 20 SPA, standalone components, signals-first (no NgRx), RxJS only at edges (`toSignal` at the store boundary), Angular Material + SCSS tokens, Storybook. Feature-based tree: `core/` (singletons) · `shared/` (stateless, no feature imports) · `features/<feature>/{pages,components,data,*.routes.ts}` lazy-loaded · `styles/` · `storybook/`.

**Invariants that drive most of the design** — know these before touching loan/book/user rules:
- `books.available_copies` is a deliberately denormalized maintained counter, system-managed, mutated only transactionally by the loan lifecycle, guarded by a CHECK constraint and nightly reconciliation.
- Check-out takes a pessimistic row lock (`SELECT … FOR UPDATE`) so the last copy cannot be oversold.
- Loan status (`active`/`overdue`/`returned`) is derived, never stored.
- Deletes are soft (books, users) to preserve loan history; deletion is blocked while active loans/references exist.
- The system must always retain ≥1 active Administrator.
- A user carries exactly one role: writes take a single `role`, reads return `roles[]` plus effective `permissions[]`. Role changes and deactivation revoke every token immediately.

**Error and validation split** — one envelope `{"error":{code,message,details,trace_id}}`; `422` = malformed input (FormRequests), `409` = valid input rejected by business state (domain exceptions carrying stable codes). Codes are registered in `04-api-specification.md` §10; keep that registry, the `BR-*` rules, and the exception mapping table in the RFC §7 in sync.

**Deliberately deferred** (do not slip into MVP scope): Redis caching (seam prepared, dependency absent), Meilisearch/Scout (DB-native FULLTEXT behind `BookSearchInterface`), and all AI features — those belong only under "Future Enhancements".

## Backend specifics

- Local development and the test suite run on **SQLite**; MariaDB is the production target. Migrations guard MariaDB-only features (FULLTEXT, CHECK constraints) behind driver checks, and tests that need them (the check-out row lock) skip themselves rather than pretending to pass.
- The OpenAPI document is generated from the code by Scramble and committed as `backend/openapi.json`. A test fails if it drifts — run `composer openapi` after changing a route, FormRequest or Resource.
- Business constants (loan limit, loan window, pagination caps, minimum search length) live in `backend/config/library.php`, not in the rules.

## Commits

Conventional-commit prefixes (`docs:`, `feat(backend):`, `chore(backend):`), imperative subject, body explaining what and why. Always English.
