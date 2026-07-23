# Product Requirements Document (PRD) — Frontend
**Product:** Librarium Web App (Angular 20 SPA)
**Status:** Approved for Implementation • **Version:** 1.0 • **Last Updated:** 2026-07-22
**Audience:** Frontend Engineering, Design, QA

---

## 1. Overview

The Librarium web app is an Angular 20 single-page application consuming the Librarium REST API (`/api/v1`). It serves three roles — Administrator, Librarian, Member — from one codebase, with role-driven navigation and route guards. The design ethos: **desk-speed for staff, clarity for members**. Built with standalone components, Signals-first state, RxJS for streams, Angular Material as the component base, SCSS design tokens, and Storybook as the living component catalog.

## 2. Navigation & Layouts

### 2.1 Application shells (layouts)
- **AuthLayout** — centered card on a neutral background. Routes: `/login`, `/register`. No nav.
- **AppLayout** — the authenticated shell:
  - **Top bar:** product logo, **global search input** (books; keyboard shortcut `/`), user menu (profile, change password, theme toggle, logout).
  - **Side nav (Material `mat-sidenav`):** role-filtered items:
    - Admin: Dashboard, Books, Authors, Categories, Loans, Users, Reports
    - Librarian: Dashboard, Books, Authors, Categories, Loans, Members
    - Member: Catalog (Books), My Loans
  - **Content area:** routed feature pages with a consistent page-header pattern (title, breadcrumb, primary action button).
- **ErrorLayout** — 404 / 403 / 500 full-page states with recovery actions.

### 2.2 Route map (top-level)
```
/login /register
/dashboard                         (admin, librarian)
/books /books/new /books/:id /books/:id/edit
/authors ... /categories ...       (list/new/detail/edit; new+edit staff-only)
/loans /loans/checkout             (staff)  /my-loans (member)
/users /users/new /users/:id/edit  (admin; librarian: member creation only)
/reports                           (admin)
/search?q=                         (all roles)
/forbidden /not-found
```

### 2.3 Responsive behavior
- **Breakpoints:** `<600px` handset, `600–1024px` tablet, `>1024px` desktop (Material CDK `BreakpointObserver`).
- Side nav: permanent (desktop) → collapsible rail (tablet) → overlay drawer + hamburger (handset).
- Data tables degrade to **card lists** on handsets (each row → summary card with primary/secondary text and action menu). Tables never horizontally scroll for core columns.
- Forms: two-column desktop → single-column mobile; sticky action bar (Save/Cancel) on mobile.
- Dashboard KPI grid: 4 → 2 → 1 columns.

## 3. Authentication Flow & UX

1. Unauthenticated user hits any protected route → `authGuard` redirects to `/login?redirect=<url>`.
2. Login form (email, password) with inline validation; on submit → `POST /auth/login`; token stored in memory + `localStorage` (documented XSS trade-off; interceptor attaches `Authorization` header).
3. On success → fetch `/me`, hydrate `AuthStore` (signal state: `user`, `role`, `isAuthenticated`) → redirect to role home (staff → `/dashboard`, member → `/books`).
4. Registration (public) creates a Member and auto-logs-in.
5. **401 from any API call** → interceptor clears session, routes to `/login` with a "session expired" toast. **403** → routes to `/forbidden` (or inline toast if triggered from an in-page action).
6. Logout → `POST /auth/logout`, clear store/storage, redirect `/login`.
7. UI affordances are role-gated with a `*hasRole` structural directive — but the API remains the actual enforcement layer; the UI only hides what would fail.

## 4. Feature Modules (functional requirements)

### 4.1 Dashboard (staff)
- KPI stat cards: Total Books, Available, Borrowed Now, Overdue (red accent), Members, Books Added This Month, Loans This Month, Returns This Month.
- Charts (`ng2-charts`/Chart.js wrapper components): Books by Category (doughnut), Monthly Loans vs Returns (12-mo line), Popular Authors (horizontal bar).
- Recent Activity feed (last 20 events, relative timestamps) and Recently Added Books list with links.
- Each widget loads independently with its own skeleton + error state; one failing widget never blanks the page.

### 4.2 Books Module
- **List:** Material table (cover thumb, title, authors, categories as chips, ISBN, availability badge `available/total`, actions). Server-side pagination/sort. Filter bar: text `q`, category select, author autocomplete, availability toggle, year range. Filters sync to query params (shareable URLs).
- **Detail:** cover, metadata, availability, category chips → filtered list; staff see active-loans count and quick **Check out** action.
- **Create/Edit (staff):** reactive form with ISBN input (auto-normalizes, checksum validation mirrored client-side), authors/categories multi-select autocomplete with inline "create new" quick-add dialog, copies stepper. Edit disables `available_copies` (server-managed) and explains why via hint text.
- **Delete (staff):** confirmation dialog; if API returns `409 BOOK_HAS_ACTIVE_LOANS`, dialog shows the specific reason and a link to the book's active loans.

### 4.3 Authors Module
- List (name, books count, actions), search-as-you-type (debounced 300ms), create/edit dialog forms (they're small), delete with in-use guard messaging.

### 4.4 Categories Module
- Same pattern as Authors; list shows books count; delete guarded by `409 CATEGORY_IN_USE` explanation.

### 4.5 Loans Module (staff) & My Loans (member)
- **Check-out flow (the money path — optimized for the desk):** single screen: member autocomplete (name/email) → member summary panel (active loans, limit remaining, overdue warning) → book autocomplete (title/ISBN — scanner-friendly: full ISBN paste triggers instant lookup) → due date picker (default +14d) → confirm. All business-rule failures (`LOAN_LIMIT_REACHED`, `LOAN_NO_COPIES`, `LOAN_MEMBER_OVERDUE`, `LOAN_DUPLICATE_TITLE`) render as inline alerts naming the rule in plain language, not as toasts.
- **Loans list (staff):** tabs All / Active / Overdue / Returned; columns: member, book, checked out, due (overdue rows highlighted, "X days overdue"), status chip, **Check in** action with confirm.
- **My Loans (member):** Active (cards with due-date countdown, overdue alert banner) and History tabs.

### 4.6 Users Module (admin; librarian restricted)
- List with role chips, active status, search/filter; create/edit forms with role select (admin only); deactivate/delete with confirmations; server `409` messages (last-admin, active-loans) surfaced verbatim in the dialog.

### 4.7 Search
- Global top-bar search → `/search?q=`: results as book cards with match context; same filter bar as Books list; empty state offers spelling tips and "browse categories" links.

## 5. UX States (system-wide standards)

| State | Standard |
|---|---|
| **Loading** | Route-level: skeleton screens (never blank). In-component: Material progress bar on tables during re-fetch (data stays visible, dimmed). Buttons show inline spinner + disabled during mutations. |
| **Skeletons** | Dedicated `ui-skeleton-*` components (table rows ×5, stat card, detail page, card grid) matching real layout geometry to prevent CLS. Shimmer animation, `prefers-reduced-motion` honored. |
| **Empty** | Illustration + one-line explanation + primary CTA ("No books yet → Add your first book" for staff; neutral copy for members). Filtered-empty differs from true-empty ("No results for these filters → Clear filters"). |
| **Error** | Inline retry cards for widget/list load failures ("Couldn't load loans. **Retry**"); full error pages only for route-level failures. Error copy never shows raw codes; `trace_id` available under a "details" expander for support. |
| **Toasts** | Material Snackbar, bottom-center, 4s. Success = confirmation of mutations ("Book created"). Errors that block a flow are inline, not toasts. Max one visible; queued. Includes "Undo" only where safe (none in MVP). |
| **Confirmation dialogs** | Required for all destructive actions (delete, deactivate, check-in is *not* destructive → no dialog friction, uses single-click + toast with the loan summary). Destructive confirm buttons use warn color and name the object ("Delete *Dune*?"). |

## 6. Accessibility (WCAG 2.2 AA target)

- Full keyboard operability; visible focus rings; logical tab order; `/` focuses global search; dialogs trap focus and restore on close (Material CDK a11y).
- Semantic landmarks (`header/nav/main`), one `h1` per page, table headers with `scope`, form fields with programmatic labels and `aria-describedby` errors announced via live regions.
- Color contrast ≥ 4.5:1 in both themes; status never conveyed by color alone (overdue = color + icon + text).
- Route changes announce the new page title (`Title` strategy + `LiveAnnouncer`).
- All Storybook stories run axe checks in CI (`@storybook/addon-a11y`).

## 7. Dark Mode Readiness

- All colors via CSS custom properties over Material theming (M3 color tokens); light theme ships default; `.dark-theme` class toggles token values.
- Theme toggle in user menu; persisted in `localStorage`; initial value respects `prefers-color-scheme`.
- No hard-coded hex in feature styles (lint rule); charts read palette from tokens.

## 8. Architecture: components, state, and conventions

### 8.1 Component hierarchy & reusable components
- **Smart (container) pages** own data fetching/state (e.g., `BooksListPage`); **presentational components** are input/output-only and Storybook-documented.
- Shared UI kit (`shared/ui`): `PageHeader`, `DataTable` (generic, server-pagination-aware), `FilterBar`, `SearchInput` (debounced), `StatCard`, `StatusChip`, `AvailabilityBadge`, `EmptyState`, `ErrorState`, `Skeleton*`, `ConfirmDialog`, `EntityAutocomplete` (generic async autocomplete used for members/books/authors), `RoleBadge`, `DueDateIndicator`.
- Composition over inheritance; every shared component has stories + a11y checks.

### 8.2 Design system
- SCSS token layers: `tokens` (color, spacing 4px scale, radius, elevation, type ramp) → `theme` (Material M3 theme wiring) → `components`. Typography: Material type scale, single font family.
- Documented in Storybook "Design System" section (colors, spacing, type, iconography = Material Symbols).

### 8.3 State management — Signals first, RxJS at the edges
- **Signals** hold component/feature state: signal stores per feature (`BooksStore`: `books`, `filters`, `pagination`, `loading`, `error` as signals; `computed` for derived values like `hasResults`; `effect` for URL-sync).
- **RxJS** handles event streams and async composition: HTTP (`HttpClient` returns observables → `toSignal` at the boundary), debounced type-ahead (`valueChanges.pipe(debounceTime, switchMap)`), router param streams. Rule of thumb: *state = signal, stream = observable, convert at the boundary.*
- `AuthStore` is app-singleton (provided in root): `user = signal<User|null>`, `role = computed`, exposed readonly.
- No NgRx in MVP — deliberate: app state is modest, signal stores are sufficient, and this reduces boilerplate. Revisit if cross-feature state coupling grows.

### 8.4 Routing, guards, interceptors
- Standalone lazy routes per feature (`loadChildren` on route arrays); typed route params.
- **Guards (functional):** `authGuard` (session), `roleGuard(['admin','librarian'])` (authorization redirect to `/forbidden`), `pendingChangesGuard` (dirty-form confirm on exit).
- **Interceptors (functional):** `authTokenInterceptor` (attach bearer), `apiErrorInterceptor` (401/403/5xx mapping, toast/error routing, `trace_id` capture), `loadingInterceptor` (optional global bar).

### 8.5 Storybook organization & usage
- Structure: `Design System/*` (tokens, typography, colors) → `Shared UI/*` (every reusable component, all states: default/loading/empty/error/disabled) → `Features/*` (key presentational compositions, e.g., book card, loan row) → `Pages/*` (assembled page states with mocked stores, for design review).
- Usage: Storybook is the **component contract** — a shared component is "done" when its stories cover all states and pass a11y + interaction tests (`@storybook/test`); Chromatic (or equivalent) visual regression in CI is a Should-have.

## 9. Non-functional (frontend)

- Initial route bundle < 250KB gz (lazy features, standalone tree-shaking); LCP < 2.5s on mid-tier hardware; route-level code splitting mandatory.
- All list interactions optimistic-free (server truth) in MVP; explicit re-fetch after mutations.
- Copy externalized to a strings layer for future i18n (en/es); dates rendered in user locale from ISO-8601 UTC API values.

---
*End of Frontend PRD.*
