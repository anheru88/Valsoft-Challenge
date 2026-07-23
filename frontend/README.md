# Librarium — Frontend (Angular 20)

The library management SPA. Implements `docs/02-prd-frontend.md` and consumes
the API in `../backend`.

## Getting started

```bash
npm install
npm start                  # http://localhost:4200
npm start -- --port 4300   # if something else already holds 4200
```

`ng serve` proxies to `http://localhost:8000` (see `proxy.conf.json`), so with
the backend running (`php artisan serve`) requests to `/api` reach Laravel
without tripping over CORS.

## Server configuration

The API URL lives in exactly one place:

| File | Used by | `apiUrl` |
|---|---|---|
| `src/environments/environment.ts` | production (`ng build`) | `/api/v1` |
| `src/environments/environment.development.ts` | development (`npm start`) | `http://localhost:8000/api/v1` |

The swap is done by `fileReplacements` in `angular.json`. Services ask for
relative paths — `books`, `loans/12/return` — and `apiBaseInterceptor` resolves
them against that base; `authTokenInterceptor` attaches the token **only** to
requests aimed at our own API, so the credential is never handed to a third
party.

Pointing at a different server is a one-line change to `apiUrl`; no service
notices.

## Commands

```bash
npm start              # development server
npm run build          # production build
npm run storybook      # component catalogue on :6006
npm run stories:check  # opens every story in a real browser, fails if any breaks
```

`stories:check` needs Storybook running. Compiling is not rendering: a template
can compile and still break at runtime, and this check is what makes the
catalogue the component contract PRD §8.5 asks for.

## Layout

```
src/
├── environments/            # apiUrl per environment
├── styles/
│   ├── tokens.scss          # colour, type, spacing, dark mode
│   ├── base.scss            # reset and utilities (.page, .card, .lib-table…)
│   └── material.scss        # Angular Material theme mapped onto the tokens
└── app/
    ├── core/
    │   ├── models.ts        # /api/v1 types
    │   ├── auth.store.ts    # session signals; decides by capability, not by role
    │   ├── app.routes.ts    # lazy routes guarded by capability
    │   ├── guards/          # authGuard, permissionGuard, roleGuard
    │   ├── interceptors/    # API base, bearer token, global 401/403/5xx
    │   └── layouts/         # AppLayout (sidenav + topbar) and AuthLayout
    ├── shared/ui/           # design system: 12 standalone components
    └── features/            # auth, dashboard, books, authors, categories,
                             # loans, users, search, errors
```

## Authorization in the interface

The API returns `roles[]` and the account's effective `permissions[]`. The UI
decides by capability — `auth.can('catalog.manage')` — never by role name, so a
role defined on the server works without deploying the client.

Every menu entry opens on the same capability its route requires, which is what
stops the menu from offering a destination the guard will refuse. Real
authorization is always applied by the API; this only avoids offering actions
that are known to fail.

## Storybook

Four levels, per PRD §8.5:

- `Design System/*` — the tokens: palette, type scale, spacing.
- `Shared UI/*` — the 12 reusable components in all their states.
- `Pages/*` — whole screens with a mocked session, in the states §5 requires:
  skeleton while loading, loaded, empty — telling "nothing yet" apart from "no
  results for these filters" — and error with retry. Several are also told as a
  member.

The topbar theme toggle flips the `dark-theme` class, the way the application
does.

## Connecting to the API

The views currently run on **demo data**. Every integration point is marked
`// TODO API:` with its method, route and the business codes to handle
(`LOAN_LIMIT_REACHED`, `BOOK_HAS_ACTIVE_LOANS`, `LAST_ADMIN_PROTECTED`). The
full contract is in `../docs/04-api-specification.md` and its generated twin in
`../backend/openapi.json`.

Conventions:

- Business errors (409) → `<lib-inline-alert>` next to the control, never a toast.
- Validation errors (422) → `fieldErrors` signal mapped to a per-field `mat-error`.
- 401/403/5xx → handled globally by `api-error.interceptor`.

## Visual identity

- **Palette**: library-lamp green `#2F6B4F` (primary), brass `#A97E2F` (accent),
  stamp red `#B3402A` (overdue and destructive actions), paper `#F5F6F2`.
- **Type**: Fraunces (headings), Public Sans (interface), IBM Plex Mono (ISBNs,
  dates and codes — catalogue-card data is always monospaced).
- **Signature element**: `<lib-due-stamp>`, the due-date stamp with its inked
  rotation. Green when active, red when overdue, dotted grey when returned.
- **Dark mode**: a `dark-theme` class on `<body>`; every component inherits it
  through the tokens.

## Note on language

Code, comments and documentation are written in English. The user-facing copy in
the templates is Spanish, which is the product's own language.
