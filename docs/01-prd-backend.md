# Product Requirements Document (PRD) — Backend
**Product:** Librarium — Mini Library Management System
**Document Owner:** Product Management
**Status:** Approved for Implementation
**Version:** 1.0
**Last Updated:** 2026-07-22
**Audience:** Backend Engineering, QA, DevOps, Security

---

## 1. Executive Summary

Librarium is a multi-role library management platform delivered as a REST API (Laravel 12 / PHP 8.4 / MariaDB) consumed by an Angular 20 SPA. The MVP delivers full lifecycle management of a library's catalog (books, authors, categories), circulation (check-in / check-out via loans), user and role management, search, and an operational dashboard.

Although the immediate driver is a technical assessment, the product is specified as a production-grade SaaS MVP: versioned API, token authentication with role-based authorization, auditable business rules, defensive validation, structured error contracts, and a documented path to horizontal scale. The backend is architected as a **Modular Monolith with DDD-Lite** (see the Architecture RFC), which keeps operational complexity low while preserving clean module boundaries for future extraction.

Success for v1 means: a librarian can run day-to-day circulation without workarounds, an administrator can govern users and data with confidence, and a member can find and track their books — all through a stable, documented, secure API.

## 2. Product Vision

**Vision statement:** *Give small and mid-size libraries the operational software quality of a modern SaaS company — a fast, reliable, and pleasant system for managing collections and circulation — without enterprise pricing or enterprise complexity.*

Guiding principles:

1. **Circulation is the heartbeat.** Check-out and check-in must be the fastest, most reliable flows in the system. Every design decision is evaluated against "does this slow down the front desk?"
2. **Data integrity over convenience.** Loans, availability counts, and user records are the library's ledger. The system must never allow an impossible state (e.g., negative available copies).
3. **Boring, predictable API.** Consistent envelopes, consistent errors, consistent pagination. Client engineers should be able to guess an endpoint's behavior correctly.
4. **Built to grow, shipped small.** Redis, full-text search engines, and AI features are designed-for but deliberately excluded from the MVP.

## 3. Business Goals

| ID | Goal | Success Metric (MVP) |
|----|------|----------------------|
| BG-1 | Enable complete circulation workflows digitally | 100% of check-in/out operations executable via API without manual DB edits |
| BG-2 | Reduce catalog administration time | Book create/edit/delete achievable in < 3 API calls end-to-end |
| BG-3 | Provide operational visibility to management | Dashboard exposes ≥ 8 KPIs with < 1s response time |
| BG-4 | Establish trustworthy multi-user access | Zero privilege-escalation paths in security review; 3 distinct roles enforced server-side |
| BG-5 | Position for future monetization/expansion | Architecture review confirms modules are extractable without rewrite; AI features documented as roadmap |

## 4. Technical Goals

| ID | Goal | Measure |
|----|------|---------|
| TG-1 | Clean separation of business logic from framework | ≥ 90% of domain logic lives under `app/Library/**`, framework-free where practical |
| TG-2 | Test confidence | ≥ 80% coverage on Domain/Application layers; feature tests for every endpoint |
| TG-3 | API contract stability | OpenAPI 3.1 spec generated and committed; breaking changes require version bump (`/api/v1`) |
| TG-4 | Performance baseline | p95 < 300ms for list endpoints at 10k books / 1k users seed data; zero N+1 queries (asserted in tests) |
| TG-5 | Security baseline | OWASP API Top 10 checklist passed; all mutations authorized by Policies; rate limiting on auth endpoints |
| TG-6 | Operational readiness | Structured JSON logging, request correlation IDs, health-check endpoint |

## 5. Scope

### 5.1 In Scope (MVP)

- **Authentication:** registration (member self-service), login, logout, token management via Laravel Sanctum, password change.
- **Users & Roles:** CRUD on users (Admin), three fixed roles (Administrator, Librarian, Member), role assignment.
- **Permissions:** role-based authorization enforced via Policies on every mutating and sensitive read endpoint.
- **Books:** CRUD, multiple copies per title, availability tracking, ISBN validation, cover URL, publisher, publication year.
- **Authors:** CRUD, many-to-many with books.
- **Categories:** CRUD, many-to-many with books.
- **Loans (Check-out / Check-in):** create loan (check-out), return loan (check-in), due dates, overdue detection, loan history, per-member limits.
- **Search:** multi-field search across title, ISBN, author, category, description, publisher with filtering and sorting.
- **Dashboard:** aggregated KPIs and activity feeds (see §11 of the RFC and FR-DASH below).
- **Reports (lightweight):** overdue loans report, most-borrowed books, activity within a date range.
- **Cross-cutting:** pagination, sorting, filtering, validation, structured error handling, API versioning, logging, rate limiting, seeding for demo data.

### 5.2 Out of Scope (MVP)

- All AI features (recommendations, summaries, classification, NL search, chat assistant, duplicate detection) — documented as Future Enhancements only.
- Fines/fees, payments, invoicing.
- Reservations / hold queues.
- Email/SMS notifications (overdue reminders).
- Multi-branch / multi-tenant library support.
- Physical hardware integration (barcode scanners, RFID) — though ISBN lookup is designed to be scanner-friendly.
- Import/export (MARC, CSV bulk import).
- Localization of content (API is localization-*ready*, not localized).
- OAuth / social login; SSO.
- Redis-backed caching in production paths (infrastructure prepared, not enabled).

## 6. Personas

### 6.1 Administrator — "Alicia"
Library director / IT owner. Accountable for data governance, staffing access, and reporting to the board. Uses the system weekly for user management and monthly for reports. **Needs:** total control over users and data, confidence nothing is deleted accidentally, clear audit trail. **Pain points today:** spreadsheets with no access control; no visibility into circulation trends.

### 6.2 Librarian — "Luis"
Front-desk staff. Uses the system continuously during opening hours. Checks books in/out, registers walk-in members, maintains the catalog. **Needs:** speed (a check-out is a queue of people), forgiving UX with strict server safety, quick search by anything (title fragment, ISBN scan, member name). **Pain points today:** manual card systems, no way to know if a copy is available without walking to the shelf.

### 6.3 Member — "Marta"
Library patron. Interacts occasionally via the web app. Searches the catalog, checks availability, views her active loans and due dates, and her borrowing history. **Needs:** trustworthy availability info, clarity on due dates and limits. **Cannot** modify catalog data or see other members' data.

## 7. User Stories

Format: `US-<domain>-<n>`. Priority: **M**ust / **S**hould / **C**ould (MoSCoW). All Must stories are MVP-blocking.

### Authentication & Account
| ID | Story | Priority |
|----|-------|----------|
| US-AUTH-1 | As a **Member**, I want to register with my name, email and password so that I can use the library online. | M |
| US-AUTH-2 | As **any user**, I want to log in with email and password and receive an access token so that I can call protected endpoints. | M |
| US-AUTH-3 | As **any user**, I want to log out so that my token is revoked and my session cannot be reused. | M |
| US-AUTH-4 | As **any user**, I want to view my own profile so that I can confirm my account details and role. | M |
| US-AUTH-5 | As **any user**, I want to change my password (providing the current one) so that I can keep my account secure. | S |

### Users & Roles
| ID | Story | Priority |
|----|-------|----------|
| US-USER-1 | As an **Administrator**, I want to list, search, and paginate all users so that I can manage the community. | M |
| US-USER-2 | As an **Administrator**, I want to create users with any role so that I can onboard staff and members. | M |
| US-USER-3 | As an **Administrator**, I want to edit a user's details and role so that I can correct data and promote/demote staff. | M |
| US-USER-4 | As an **Administrator**, I want to deactivate or delete a user (with safety checks) so that departed users lose access. | M |
| US-LIBR-1 | As a **Librarian**, I want to register new members at the front desk so that walk-ins can borrow immediately. | M |
| US-LIBR-2 | As a **Librarian**, I want to look up a member and see their active loans so that I can serve them at the desk. | M |

### Books, Authors, Categories
| ID | Story | Priority |
|----|-------|----------|
| US-BOOK-1 | As a **Librarian**, I want to create a book with ISBN, authors, categories, and number of copies so that the catalog reflects our shelves. | M |
| US-BOOK-2 | As a **Librarian**, I want to edit any book field so that I can fix mistakes and update copy counts. | M |
| US-BOOK-3 | As a **Librarian/Admin**, I want to delete a book that has no active loans so that the catalog stays clean. | M |
| US-BOOK-4 | As a **Member**, I want to view book details including current availability so that I know whether to visit. | M |
| US-AUTH0R-1 | As a **Librarian**, I want to manage authors (CRUD) so that books can be correctly attributed. | M |
| US-CAT-1 | As a **Librarian**, I want to manage categories (CRUD) so that the catalog is browsable by subject. | M |

### Loans (Circulation)
| ID | Story | Priority |
|----|-------|----------|
| US-LOAN-1 | As a **Librarian**, I want to check out a book to a member so that circulation is recorded and availability decremented. | M |
| US-LOAN-2 | As a **Librarian**, I want to check in a returned book so that availability is restored and the loan closed. | M |
| US-LOAN-3 | As a **Librarian**, I want the system to block a check-out when the member is at their loan limit or the book has no available copies, with a clear error, so that policy is enforced automatically. | M |
| US-LOAN-4 | As a **Member**, I want to see my active loans with due dates and overdue status so that I can return on time. | M |
| US-LOAN-5 | As a **Member**, I want to see my full borrowing history so that I can recall what I've read. | S |
| US-LOAN-6 | As a **Librarian/Admin**, I want to list all overdue loans so that I can follow up with members. | M |

### Search
| ID | Story | Priority |
|----|-------|----------|
| US-SRCH-1 | As **any user**, I want to search books by title, ISBN, author, category, description, or publisher in one query so that I find items fast. | M |
| US-SRCH-2 | As **any user**, I want to combine search with filters (category, availability, year) and sorting so that I can narrow results. | M |

### Dashboard & Reports
| ID | Story | Priority |
|----|-------|----------|
| US-DASH-1 | As an **Admin/Librarian**, I want a dashboard of KPIs (totals, borrowed, overdue, recent activity, popular authors, books by category, monthly stats) so that I understand library health at a glance. | M |
| US-RPT-1 | As an **Administrator**, I want a most-borrowed-books report over a date range so that I can plan acquisitions. | S |

## 8. Functional Requirements

Each requirement is uniquely identified (`FR-*`) and testable.

### 8.1 Authentication (FR-AUTH)
- **FR-AUTH-1:** The system SHALL authenticate via Laravel Sanctum personal access tokens; tokens returned on login/registration, sent as `Authorization: Bearer <token>`.
- **FR-AUTH-2:** Public registration SHALL create a user with role `member` only. Role selection at public registration is forbidden.
- **FR-AUTH-3:** Login SHALL rate-limit to 5 attempts/minute per email+IP; excess returns `429`.
- **FR-AUTH-4:** Logout SHALL revoke the current access token (not all tokens).
- **FR-AUTH-5:** Passwords SHALL be hashed with bcrypt/argon2id (framework default), min length 8, require at least one letter and one number.
- **FR-AUTH-6:** `GET /me` SHALL return the authenticated user with role and active-loan count.
- **FR-AUTH-7:** Password change SHALL require the current password and revoke all other tokens on success.

### 8.2 Users (FR-USER)
- **FR-USER-1:** Admin SHALL list users with pagination, sorting (`name`, `email`, `created_at`), and filters (`role`, `is_active`, free-text `q` on name/email).
- **FR-USER-2:** Admin SHALL create users with name, email (unique), password, role. Librarian SHALL create only `member` users.
- **FR-USER-3:** Admin SHALL update any user; users SHALL update their own name/email; role changes are Admin-only.
- **FR-USER-4:** User deletion is Admin-only; deletion SHALL be blocked (`409`) if the user has active loans; the system SHALL use soft deletes to preserve loan history integrity.
- **FR-USER-5:** An Admin SHALL NOT delete or demote their own account if they are the last active Administrator (`409`).
- **FR-USER-6:** Deactivated users (`is_active = false`) SHALL be unable to authenticate; existing tokens are revoked on deactivation.

### 8.3 Roles & Permissions (FR-PERM)
- **FR-PERM-1:** Exactly three roles exist in MVP: `admin`, `librarian`, `member`, stored as an enum on the user record. (Single-role model chosen deliberately for MVP simplicity; a `roles`/`permissions` table is a documented future migration.)
- **FR-PERM-2:** Authorization SHALL be enforced server-side by Laravel Policies on every controller action; the permission matrix below is the source of truth.
- **FR-PERM-3:** Authorization failures SHALL return `403` with the standard error envelope; unauthenticated access to protected routes returns `401`.

**Permission matrix (MVP):**

| Capability | Admin | Librarian | Member |
|---|---|---|---|
| View catalog / search | ✅ | ✅ | ✅ |
| Create/edit books, authors, categories | ✅ | ✅ | ❌ |
| Delete books/authors/categories | ✅ | ✅ | ❌ |
| Check out / check in loans | ✅ | ✅ | ❌ |
| View any member's loans | ✅ | ✅ | ❌ |
| View own loans | ✅ | ✅ | ✅ |
| List/create users (any role) | ✅ | ❌ | ❌ |
| Create member users | ✅ | ✅ | ❌ |
| Edit/delete users, change roles | ✅ | ❌ | ❌ |
| View dashboard & reports | ✅ | ✅ | ❌ |

### 8.4 Books (FR-BOOK)
- **FR-BOOK-1:** A book SHALL have: `title` (req, ≤255), `isbn` (req, unique, ISBN-10 or ISBN-13 checksum-validated), `description` (opt, ≤5000), `publisher` (opt, ≤255), `publication_year` (opt, 1450..current year), `cover_url` (opt, valid URL), `total_copies` (req, ≥1), `available_copies` (derived/maintained, 0..total), authors (≥1), categories (≥1).
- **FR-BOOK-2:** `available_copies` SHALL be maintained transactionally by the loan lifecycle and never directly writable via the API.
- **FR-BOOK-3:** Reducing `total_copies` below the number of currently loaned copies SHALL be rejected (`422`).
- **FR-BOOK-4:** Book deletion SHALL be blocked while active (non-returned) loans exist (`409`); books use soft deletes so historical loans keep their reference.
- **FR-BOOK-5:** Book list SHALL support pagination, sorting (`title`, `publication_year`, `created_at`, `available_copies`), and filters (`category_id`, `author_id`, `available` boolean, `year_from`/`year_to`, free-text `q`).
- **FR-BOOK-6:** Book detail SHALL embed authors, categories, availability, and (for staff) active loan count.

### 8.5 Authors (FR-AUTHOR)
- **FR-AUTHOR-1:** Author fields: `name` (req, ≤255), `bio` (opt, ≤2000), `birth_year` (opt). Uniqueness is advisory (warning surfaced via duplicate-name lookup endpoint), not enforced — real-world author homonyms exist.
- **FR-AUTHOR-2:** Deleting an author attached to ≥1 book SHALL be blocked (`409`) until reassigned/detached.
- **FR-AUTHOR-3:** Author list supports pagination, `q` search on name, sort by `name`/`created_at`, and an embedded `books_count`.

### 8.6 Categories (FR-CAT)
- **FR-CAT-1:** Category fields: `name` (req, unique, ≤100), `slug` (system-generated, unique), `description` (opt, ≤500).
- **FR-CAT-2:** Deleting a category attached to ≥1 book SHALL be blocked (`409`).
- **FR-CAT-3:** Category list supports `q` on name, `books_count`, sorting by `name`/`books_count`.

### 8.7 Loans (FR-LOAN)
- **FR-LOAN-1 (Check-out):** Staff SHALL create a loan for (`user_id`, `book_id`) with optional `due_date` (default: today + 14 days, max: today + 60 days). On success, `available_copies` decrements atomically.
- **FR-LOAN-2 (Check-in):** Staff SHALL mark a loan returned; `returned_at` is set, `available_copies` increments atomically. Returning an already-returned loan yields `409`.
- **FR-LOAN-3:** Loan statuses are derived: `active` (not returned, not past due), `overdue` (not returned, past due), `returned`. Status is computed, never stored redundantly, to avoid drift.
- **FR-LOAN-4:** Concurrency: check-out SHALL use a pessimistic row lock (or conditional decrement) so simultaneous check-outs of the last copy cannot oversell (`409 LOAN_NO_COPIES` for the loser).
- **FR-LOAN-5:** Loan list (staff) supports filters: `status`, `user_id`, `book_id`, `overdue=true`, date ranges; members see only their own loans regardless of parameters.
- **FR-LOAN-6:** A member SHALL NOT hold two active loans of the same book title simultaneously.

### 8.8 Search (FR-SRCH)
- **FR-SRCH-1:** `GET /api/v1/search/books?q=` SHALL match against title, ISBN (exact-normalized), author name, category name, description, publisher using indexed `LIKE 'term%'` / full-text where beneficial (see RFC §Search Strategy).
- **FR-SRCH-2:** Search SHALL be combinable with all book filters and sorting; results use the standard paginated envelope; relevance ordering when `q` present, otherwise requested sort.
- **FR-SRCH-3:** Empty `q` with filters is valid (filter-only browse). `q` shorter than 2 chars (non-ISBN) returns `422`.

### 8.9 Dashboard (FR-DASH)
- **FR-DASH-1:** `GET /api/v1/dashboard` (staff-only) SHALL return in one response: `total_books`, `total_copies`, `available_copies`, `borrowed_now`, `overdue_now`, `total_members`, `books_added_this_month`, `loans_this_month`, `returns_this_month`.
- **FR-DASH-2:** Companion endpoints SHALL provide: `recent-activity` (last 20 loan/return/book events), `popular-authors` (top 5 by loans, window param), `recent-books` (last 10 added), `books-by-category` (counts), `monthly-stats` (12-month loans vs. returns series).
- **FR-DASH-3:** Dashboard queries SHALL be aggregate SQL (no N+1); each endpoint p95 < 500ms at seed scale; response shapes are chart-ready (labels + series).

### 8.10 Reports (FR-RPT)
- **FR-RPT-1:** Overdue report: all overdue loans with member contact info, days overdue, sortable by days overdue.
- **FR-RPT-2:** Most-borrowed report: top N books by completed+active loans within `[from, to]`.

### 8.11 Pagination, Sorting, Filtering (FR-LIST)
- **FR-LIST-1:** All collection endpoints SHALL use page-based pagination: `page` (default 1), `per_page` (default 15, max 100). Envelope: `data`, `meta {current_page, per_page, total, last_page}`, `links`.
- **FR-LIST-2:** Sorting via `sort` (field) + `direction` (`asc|desc`, default `asc`); only whitelisted fields per resource; invalid field → `422`.
- **FR-LIST-3:** Filters are explicit query params per resource (documented in the API spec); unknown params are ignored (forward compatibility).

### 8.12 Validation (FR-VAL)
- **FR-VAL-1:** All input SHALL be validated in Form Requests before reaching the application layer; controllers never receive raw request arrays.
- **FR-VAL-2:** Validation failure returns `422` with `errors` keyed by field, each an array of human-readable messages, plus a stable machine `code`.
- **FR-VAL-3:** ISBN validation normalizes (strips hyphens/spaces) and verifies checksum for both ISBN-10 and ISBN-13.
- **FR-VAL-4:** All string inputs are trimmed; emails lowercased; IDs validated for existence via `exists` rules or repository checks.

### 8.13 Error Handling (FR-ERR)
- **FR-ERR-1:** Every error response SHALL follow one envelope: `{"error": {"code": "STRING_CODE", "message": "human message", "details": {...}, "trace_id": "uuid"}}`.
- **FR-ERR-2:** Domain rule violations map to `409` (conflict with business state) or `422` (invalid input) with stable codes, e.g. `LOAN_LIMIT_REACHED`, `LOAN_NO_COPIES`, `BOOK_HAS_ACTIVE_LOANS`, `LAST_ADMIN_PROTECTED`.
- **FR-ERR-3:** Unhandled exceptions return `500` with a generic message and `trace_id`; internals (stack traces, SQL) are never leaked in production responses.
- **FR-ERR-4:** `404` for missing/unauthorized-to-know resources; `405`, `429` per HTTP semantics.

## 9. Non-Functional Requirements

| Area | Requirement |
|---|---|
| **Performance** | p95 < 300ms list endpoints, < 150ms detail endpoints at 10k books/1k users/50k loans. Zero N+1 (enforced by `Model::preventLazyLoading()` in tests and automated assertions). Dashboard aggregates < 500ms. |
| **Security** | Sanctum bearer tokens; Policies on all actions; rate limiting (auth 5/min, general 60/min per user); input validation everywhere; parameterized queries only (Eloquent/Query Builder); security headers; secrets via env; OWASP API Top 10 checklist in CI review. See RFC §Security. |
| **Scalability** | Stateless API (token auth, no server sessions) → horizontally scalable behind a load balancer. Read-heavy paths designed for cache insertion (Redis-ready via `Cache` facade with tagged keys, disabled by default). DB indexes specified in RFC. Modular monolith allows domain extraction. |
| **Maintainability** | DDD-Lite module boundaries; SOLID; PSR-12 via Laravel Pint in CI; static analysis (PHPStan level 8) on `app/Library`; ADRs for decisions; ≥80% coverage on domain/application layers. |
| **Availability** | MVP target 99.5% (single region). Health endpoint `GET /up` for LB checks. Graceful degradation: dashboard failures never block circulation endpoints. Backups: nightly logical dump + binlog (ops runbook). |
| **Logging** | Structured JSON logs (Monolog); every request logged with `trace_id`, user id, route, duration, status; domain events (loan created/returned, user role changed, deletions) logged at `info` as audit trail; errors with context at `error`. No PII beyond user id in logs. |
| **API Versioning** | URI versioning: `/api/v1/...`. Additive changes allowed within v1; breaking changes require `/api/v2`. Version policy documented in the API spec. |
| **Caching** | MVP: HTTP `ETag`/`Last-Modified` on book detail; no server-side cache. Architecture prepared for Redis (cache store config, key-naming convention `librarium:v1:<domain>:<key>`, invalidation via domain events). |
| **Localization readiness** | All user-facing strings (validation messages, error messages) via Laravel lang files; `Accept-Language` honored for `en`/`es` message bundles; dates in ISO-8601 UTC, formatting is a client concern; no hard-coded strings in domain code. |
| **Accessibility (API contribution)** | Error messages are human-readable and specific (screen-reader friendly when surfaced); enumerations documented so the client can render accessible labels; pagination metadata enables accessible navigation controls. |

## 10. Business Rules

Rules are authoritative; every rule is enforced server-side and covered by tests. `BR-<domain>-<n>`.

### Loans / Circulation
- **BR-LOAN-1:** A book can be checked out only if `available_copies ≥ 1`. Violation → `409 LOAN_NO_COPIES`.
- **BR-LOAN-2:** A member may hold at most **5 active loans** simultaneously (configurable constant `MAX_ACTIVE_LOANS`). Violation → `409 LOAN_LIMIT_REACHED`.
- **BR-LOAN-3:** A member with **any overdue loan** cannot check out new books until the overdue items are returned. → `409 LOAN_MEMBER_OVERDUE`.
- **BR-LOAN-4:** A member cannot hold two active loans of the same book. → `409 LOAN_DUPLICATE_TITLE`.
- **BR-LOAN-5:** Default loan period is 14 days; staff may set a due date between 1 and 60 days from check-out. Due dates in the past are invalid (`422`).
- **BR-LOAN-6:** Only `admin` and `librarian` may create or close loans. Members never self-checkout in MVP.
- **BR-LOAN-7:** Check-out and check-in are atomic: loan row mutation and `available_copies` adjustment occur in one DB transaction; on failure, nothing changes.
- **BR-LOAN-8:** A returned loan is immutable (except by Admin data-correction, which is audited).
- **BR-LOAN-9:** Loans can only be issued to `member`-role users who are active. Staff borrowing requires a member account (keeps circulation data clean).

### Books / Catalog
- **BR-BOOK-1:** ISBN is unique across non-deleted books; checksum must validate.
- **BR-BOOK-2:** `available_copies` is system-managed; any attempt to set it directly is ignored/rejected.
- **BR-BOOK-3:** `total_copies` cannot be set below `total_copies − available_copies` (i.e., below copies currently on loan). → `422 BOOK_COPIES_BELOW_LOANED`.
- **BR-BOOK-4:** A book with active loans cannot be deleted. → `409 BOOK_HAS_ACTIVE_LOANS`.
- **BR-BOOK-5:** Every book has ≥1 author and ≥1 category at all times.
- **BR-AUTHOR-1 / BR-CAT-1:** Authors/categories referenced by any book cannot be deleted. → `409 *_IN_USE`.

### Users / Access
- **BR-USER-1:** Only Admin can delete users; deletion is soft; users with active loans cannot be deleted. → `409 USER_HAS_ACTIVE_LOANS`.
- **BR-USER-2:** Only Admin can assign or change roles. Public registration always yields `member`.
- **BR-USER-3:** The system must always retain ≥1 active Administrator (`409 LAST_ADMIN_PROTECTED` on violating delete/demote/deactivate).
- **BR-USER-4:** Librarians may create only `member` accounts.
- **BR-USER-5:** Deactivating a user revokes all their tokens immediately.
- **BR-USER-6:** Email addresses are unique among non-deleted users.

## 11. Acceptance Criteria

Given/When/Then per feature (representative set; QA expands per FR).

**Authentication**
- Given valid credentials, when I `POST /auth/login`, then I receive `200` with a bearer token and my user resource.
- Given 6 failed logins in a minute, when I try again, then I receive `429`.
- Given a valid token, when I `POST /auth/logout`, then subsequent requests with that token return `401`.

**Books**
- Given I am a Librarian, when I create a book with valid ISBN, 3 copies, one author and one category, then `201` and the book shows `available_copies = 3`.
- Given I am a Member, when I attempt `POST /books`, then `403`.
- Given a book with 2 copies on loan, when I edit `total_copies` to 1, then `422 BOOK_COPIES_BELOW_LOANED`.
- Given a book with an active loan, when I `DELETE` it, then `409 BOOK_HAS_ACTIVE_LOANS`.

**Loans**
- Given a member with 5 active loans, when a librarian checks out a 6th, then `409 LOAN_LIMIT_REACHED` and availability is unchanged.
- Given a book with `available_copies = 1` and two concurrent check-outs, then exactly one succeeds (`201`) and one fails (`409 LOAN_NO_COPIES`).
- Given an active loan, when the librarian checks it in, then `200`, `returned_at` set, availability incremented by exactly 1.
- Given a member with an overdue loan, when staff attempt a new check-out for them, then `409 LOAN_MEMBER_OVERDUE`.
- Given I am a Member, when I `GET /loans?user_id=<other>`, then I receive only my own loans.

**Search**
- Given books exist, when I `GET /search/books?q=garcía`, then results include title, author-name, and publisher matches, paginated, ordered by relevance.
- Given `q=9780307474728`, then the exact-ISBN book is the first result.

**Users**
- Given I am the last active Admin, when I demote myself, then `409 LAST_ADMIN_PROTECTED`.
- Given a deactivated user, when they call any endpoint with a pre-existing token, then `401`.

**Dashboard**
- Given seeded data, when staff `GET /dashboard`, then all KPI fields are present, numerically consistent (e.g., `available + borrowed = total_copies`), in < 500ms.
- Given I am a Member, when I `GET /dashboard`, then `403`.

**Cross-cutting**
- Any invalid payload returns `422` with per-field `errors` and a stable `code`.
- Any list endpoint honors `page`, `per_page` (capped at 100), whitelisted `sort`, and returns the standard envelope.

## 12. Future Enhancements

All items below are explicitly **not** part of the MVP.

### Future Enhancement (Optional) — AI Features
| Feature | Description | Notes |
|---|---|---|
| **AI Book Summary** | Generate/refresh `description` from title+metadata via LLM; librarian reviews before publish. | Async job; human-in-the-loop mandatory. |
| **AI Book Categorization** | Suggest categories on book creation from title/description. | Suggestions only; staff confirms. |
| **AI Recommendations** | Per-member "you may like" from loan history (collaborative + content signals). | Requires consent flag; cold-start via category popularity. |
| **Natural Language Search** | "novels about the sea published before 1960" → structured filter query. | LLM translates NL → existing search params; no new search engine required initially. |
| **Duplicate Detection** | Fuzzy match on title/author/ISBN variants at creation time to flag probable duplicates. | Embedding or trigram similarity. |
| **Chat Assistant** | Staff/member assistant grounded on catalog + policy docs. | RAG over catalog; strict permission-aware retrieval. |

### Future Enhancement (Optional) — Product
- Reservations & hold queues; overdue email/SMS notifications; fines & payments; multi-branch inventories; barcode/RFID integration; MARC/CSV import-export; member self-checkout kiosks; granular permission tables (beyond 3 fixed roles); audit-log UI; Meilisearch/OpenSearch-backed search; Redis caching enabled; webhooks & public API keys.

---
*End of Backend PRD.*
