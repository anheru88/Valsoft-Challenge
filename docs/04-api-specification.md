# Librarium REST API Specification
**Base URL:** `https://api.librarium.app/api/v1` • **Version:** 1.0 • **Format:** JSON (UTF-8)
**Status:** Approved • The generated OpenAPI 3.1 file (`openapi.yaml`) is the machine-readable twin of this document.

---

## 1. Conventions (apply to every endpoint)

**Headers**
| Header | Value | Required |
|---|---|---|
| `Accept` | `application/json` | ✅ all |
| `Content-Type` | `application/json` | ✅ on bodies |
| `Authorization` | `Bearer <token>` | ✅ except login/register |
| `Accept-Language` | `en` (default) \| `es` | optional — localizes messages |
| `X-Request-Id` | client-supplied trace id | optional — echoed as `trace_id` |

**Auth roles:** 🅰 admin • 🅻 librarian • 🅼 member. "Staff" = 🅰🅻.

**Pagination (all list endpoints):** `?page=1&per_page=15` (max 100). Envelope:
```json
{ "data": [ ... ],
  "meta": { "current_page": 1, "per_page": 15, "total": 240, "last_page": 16 },
  "links": { "first": "...?page=1", "prev": null, "next": "...?page=2", "last": "...?page=16" } }
```
**Sorting:** `?sort=<field>&direction=asc|desc` — whitelisted fields listed per endpoint; invalid → `422`.

**Error envelope (all non-2xx):**
```json
{ "error": { "code": "LOAN_LIMIT_REACHED",
             "message": "This member already has 5 active loans.",
             "details": { "limit": 5 },
             "trace_id": "8c9f1e2a-…" } }
```
**Standard status codes:** `200` OK · `201` Created (+`Location`) · `204` No Content · `401 UNAUTHENTICATED` · `403 FORBIDDEN` · `404 NOT_FOUND` · `405` · `409` domain conflict · `422 VALIDATION_FAILED` (with `details.errors` per field) · `429 RATE_LIMITED` (+`Retry-After`) · `500 INTERNAL_ERROR`.

**Validation error example (`422`):**
```json
{ "error": { "code": "VALIDATION_FAILED", "message": "The given data was invalid.",
  "details": { "errors": {
    "isbn": ["The ISBN checksum is invalid."],
    "author_ids": ["At least one author is required."] } },
  "trace_id": "…" } }
```
**Dates:** ISO-8601, UTC (`2026-07-22T14:30:00Z`); date-only fields as `YYYY-MM-DD`.

---

## 2. Authentication

### POST /auth/register — public
Creates a **member** account and logs it in. Rate limit 5/min/IP.
```json
// Request
{ "name": "Marta Ruiz", "email": "marta@example.com",
  "password": "s3curePass!", "password_confirmation": "s3curePass!" }
// 201 Response
{ "data": { "token": "1|f9K…", "token_type": "Bearer",
  "user": { "id": 42, "name": "Marta Ruiz", "email": "marta@example.com",
            "role": "member", "is_active": true, "created_at": "2026-07-22T10:00:00Z" } } }
```
Errors: `422` (email taken, weak password), `429`.

### POST /auth/login — public
`{ "email": "...", "password": "..." }` → `200` same shape as register. Errors: `422 INVALID_CREDENTIALS` (generic — no user enumeration), `403 USER_INACTIVE`, `429`.

### POST /auth/logout — 🅰🅻🅼
Revokes current token. → `204`.

### GET /auth/me — 🅰🅻🅼
```json
{ "data": { "id": 42, "name": "Marta Ruiz", "email": "…", "role": "member",
            "is_active": true, "active_loans_count": 2, "created_at": "…" } }
```

### PUT /auth/password — 🅰🅻🅼
`{ "current_password", "password", "password_confirmation" }` → `204`; revokes all other tokens. Errors: `422 CURRENT_PASSWORD_INVALID`.

---

## 3. Books

**Book resource:**
```json
{ "id": 7, "title": "One Hundred Years of Solitude", "isbn": "9780060883287",
  "description": "…", "publisher": "Harper", "publication_year": 1967,
  "cover_url": "https://…/cover.jpg",
  "total_copies": 5, "available_copies": 3, "is_available": true,
  "authors": [ { "id": 3, "name": "Gabriel García Márquez" } ],
  "categories": [ { "id": 2, "name": "Literary Fiction", "slug": "literary-fiction" } ],
  "active_loans_count": 2,            // staff only
  "created_at": "…", "updated_at": "…" }
```

| Method & route | Roles | Notes |
|---|---|---|
| `GET /books` | 🅰🅻🅼 | Filters: `q`, `category_id`, `author_id`, `available` (bool), `year_from`, `year_to`. Sort: `title`, `publication_year`, `created_at`, `available_copies`. |
| `GET /books/{id}` | 🅰🅻🅼 | Supports `ETag`/`If-None-Match` → `304`. `404` if missing/soft-deleted. |
| `POST /books` | 🅰🅻 | Body below. → `201` + `Location`. |
| `PUT /books/{id}` | 🅰🅻 | Full update, same body. `available_copies` not accepted. |
| `DELETE /books/{id}` | 🅰🅻 | → `204`. `409 BOOK_HAS_ACTIVE_LOANS` if open loans. |

**Create/Update body & validation**
```json
{ "title": "One Hundred Years of Solitude",        // required, string ≤255
  "isbn": "978-0-06-088328-7",                      // required; ISBN-10/13; checksum; unique (normalized)
  "description": "…",                               // nullable ≤5000
  "publisher": "Harper",                            // nullable ≤255
  "publication_year": 1967,                          // nullable int 1450..current
  "cover_url": "https://…",                          // nullable url
  "total_copies": 5,                                 // required int ≥1; ≥ copies currently on loan (else 422 BOOK_COPIES_BELOW_LOANED)
  "author_ids": [3],                                 // required array min:1, each exists
  "category_ids": [2] }                              // required array min:1, each exists
```

**Examples**
```
GET /api/v1/books?q=garcia&available=true&sort=title&direction=asc&page=1&per_page=15   → 200 (envelope)
POST /api/v1/books  (member token)                                                      → 403
DELETE /api/v1/books/7 (has open loan)                                                  → 409 BOOK_HAS_ACTIVE_LOANS
```

---

## 4. Authors

**Resource:** `{ "id", "name", "bio", "birth_year", "books_count", "created_at", "updated_at" }`

| Method & route | Roles | Notes |
|---|---|---|
| `GET /authors` | 🅰🅻🅼 | `q` (name prefix), sort `name`/`created_at`/`books_count`. |
| `GET /authors/{id}` | 🅰🅻🅼 | Includes paginated `books` via `?include=books`. |
| `POST /authors` | 🅰🅻 | `{ name*: ≤255, bio: ≤2000, birth_year: int }` → `201`. |
| `PUT /authors/{id}` | 🅰🅻 | Same body. |
| `DELETE /authors/{id}` | 🅰🅻 | `409 AUTHOR_IN_USE` if attached to any book; else `204`. |

---

## 5. Categories

**Resource:** `{ "id", "name", "slug", "description", "books_count", "created_at", "updated_at" }`

| Method & route | Roles | Notes |
|---|---|---|
| `GET /categories` | 🅰🅻🅼 | `q`, sort `name`/`books_count`. |
| `GET /categories/{id}` | 🅰🅻🅼 | — |
| `POST /categories` | 🅰🅻 | `{ name*: unique ≤100, description: ≤500 }`; slug generated. → `201`. |
| `PUT /categories/{id}` | 🅰🅻 | — |
| `DELETE /categories/{id}` | 🅰🅻 | `409 CATEGORY_IN_USE` or `204`. |

---

## 6. Loans (Check-out / Check-in)

**Resource:**
```json
{ "id": 501, "status": "active",                     // active | overdue | returned (derived)
  "loaned_at": "2026-07-10", "due_date": "2026-07-24", "returned_at": null,
  "days_overdue": 0,
  "user": { "id": 42, "name": "Marta Ruiz", "email": "…" },   // staff only; members get own id/name
  "book": { "id": 7, "title": "…", "isbn": "…" },
  "created_at": "…", "updated_at": "…" }
```

### POST /loans — 🅰🅻 (check-out)
```json
{ "user_id": 42, "book_id": 7, "due_date": "2026-08-05" }   // due_date optional; default +14d; range +1..+60d
```
`201` + Loan resource. Business errors (`409`): `LOAN_NO_COPIES`, `LOAN_LIMIT_REACHED` (`details.limit: 5`), `LOAN_MEMBER_OVERDUE`, `LOAN_DUPLICATE_TITLE`, `LOAN_USER_NOT_MEMBER`, `LOAN_USER_INACTIVE`. Validation (`422`): unknown ids, past/out-of-range due date.

### POST /loans/{id}/return — 🅰🅻 (check-in)
No body. `200` + updated Loan (`status: "returned"`, `returned_at` set). `409 LOAN_ALREADY_RETURNED` if closed.

### GET /loans — 🅰🅻 (all) / 🅼 (own only, params ignored for scoping)
Filters: `status=active|overdue|returned`, `user_id`, `book_id`, `overdue=true`, `loaned_from`, `loaned_to`. Sort: `loaned_at`, `due_date`, `returned_at`. Standard envelope.

### GET /loans/{id} — 🅰🅻, or 🅼 if owner (`404` otherwise — no existence leak)

### GET /users/{id}/loans — 🅰🅻 (member loan history at the desk)
Same filters/envelope as `GET /loans`.

**Example — limit reached**
```
POST /api/v1/loans  { "user_id": 42, "book_id": 9 }
→ 409
{ "error": { "code": "LOAN_LIMIT_REACHED",
             "message": "This member already has 5 active loans (limit 5).",
             "details": { "limit": 5, "active_loans": 5 }, "trace_id": "…" } }
```

---

## 7. Users

**Resource:** `{ "id", "name", "email", "roles", "permissions", "is_active", "active_loans_count", "created_at", "updated_at" }`

```json
{ "id": 42, "name": "Luis Ruiz", "email": "luis@librarium.test",
  "roles": ["librarian"],
  "permissions": ["catalog.manage", "catalog.view", "dashboard.view",
                  "loans.manage", "loans.view-any", "users.create-member", "users.view"],
  "is_active": true, "active_loans_count": 0,
  "created_at": "…", "updated_at": "…" }
```

`permissions` lists the effective capabilities of the account — from its role plus anything granted directly — so a client can hide an action it may not perform. Writes still take a single `role` (ADR-11: one role per user in the MVP).

| Method & route | Roles | Notes |
|---|---|---|
| `GET /users` | 🅰 | Filters: `q` (name/email), `role`, `is_active`; sort `name`/`email`/`created_at`. |
| `GET /users/{id}` | 🅰🅻 | Librarian: read access for desk service. |
| `POST /users` | 🅰 (any role) 🅻 (member only) | `{ name*, email* unique, password* min:8, role* in admin|librarian|member }`. 🅻 sending non-member role → `403`. → `201`. |
| `PUT /users/{id}` | 🅰 (any field) · self (name/email) | Role change 🅰-only. `409 LAST_ADMIN_PROTECTED` on demoting last admin. |
| `PATCH /users/{id}/status` | 🅰 | `{ "is_active": false }` → `200`; revokes tokens; `409 LAST_ADMIN_PROTECTED`. |
| `DELETE /users/{id}` | 🅰 | Soft delete → `204`. `409 USER_HAS_ACTIVE_LOANS` · `409 LAST_ADMIN_PROTECTED`. |

---

## 8. Search

### GET /search/books — 🅰🅻🅼
Params: `q` (min 2 chars unless ISBN-shaped), plus all `GET /books` filters and pagination. Matching: normalized-ISBN exact → title/description/publisher full-text → author/category name prefix. Ordered by relevance (or requested sort).
```
GET /api/v1/search/books?q=solitude&category_id=2&per_page=10
→ 200  standard book envelope; each item may include "matched_on": ["title"]
```
Errors: `422 SEARCH_QUERY_TOO_SHORT`.

---

## 9. Dashboard & Reports — 🅰🅻 (reports `GET /reports/*`: 🅰)

### GET /dashboard
```json
{ "data": { "total_books": 1240, "total_copies": 3480, "available_copies": 2960,
  "borrowed_now": 520, "overdue_now": 34, "total_members": 812,
  "books_added_this_month": 18, "loans_this_month": 210, "returns_this_month": 195 } }
```

| Endpoint | Returns |
|---|---|
| `GET /dashboard/recent-activity?limit=20` | `[ { "type": "loan_created"\|"loan_returned"\|"book_created", "occurred_at", "summary", "book": {…}, "user": {…} } ]` |
| `GET /dashboard/popular-authors?window=30d&limit=5` | `[ { "author": {id,name}, "loans_count" } ]` |
| `GET /dashboard/recent-books?limit=10` | latest book resources (list shape) |
| `GET /dashboard/books-by-category` | `[ { "category": {id,name}, "books_count" } ]` |
| `GET /dashboard/monthly-stats?months=12` | `{ "labels": ["2025-08", …], "loans": [ … ], "returns": [ … ] }` |
| `GET /reports/overdue` | paginated overdue loans + member contact + `days_overdue`; sort `days_overdue` |
| `GET /reports/most-borrowed?from&to&limit=10` | `[ { "book": {…}, "loans_count" } ]` |

Member access to any of the above → `403`.

---

## 10. Machine-readable error codes (registry)

`VALIDATION_FAILED` · `UNAUTHENTICATED` · `FORBIDDEN` · `NOT_FOUND` · `RATE_LIMITED` · `INTERNAL_ERROR` · `INVALID_CREDENTIALS` · `USER_INACTIVE` · `CURRENT_PASSWORD_INVALID` · `SEARCH_QUERY_TOO_SHORT` · `BOOK_HAS_ACTIVE_LOANS` · `BOOK_COPIES_BELOW_LOANED` · `AUTHOR_IN_USE` · `CATEGORY_IN_USE` · `LOAN_NO_COPIES` · `LOAN_LIMIT_REACHED` · `LOAN_MEMBER_OVERDUE` · `LOAN_DUPLICATE_TITLE` · `LOAN_ALREADY_RETURNED` · `LOAN_USER_NOT_MEMBER` · `LOAN_USER_INACTIVE` · `USER_HAS_ACTIVE_LOANS` · `LAST_ADMIN_PROTECTED`

Codes are stable within v1; new codes are additive; clients must tolerate unknown codes.

## 11. Versioning & deprecation policy

- Breaking changes (removed/renamed fields, changed semantics, tightened validation) → new URI version (`/api/v2`).
- Additive changes (new endpoints, new optional fields/params, new error codes) → allowed within v1, announced in the changelog.
- Deprecations announced ≥90 days ahead via `Deprecation` + `Sunset` headers on affected endpoints.

---
*End of API Specification.*
