import { HttpErrorResponse, HttpInterceptorFn, HttpResponse } from '@angular/common/http';
import { of, throwError } from 'rxjs';
import {
  ActivityItem, Author, Book, CategoryCount, Category, DashboardKpis, Loan,
  PopularAuthor, User,
} from '../../core/models';

/**
 * A stand-in for the API, for Storybook only.
 *
 * The pages fetch what they show, so a story that renders one has to answer its
 * requests — and answering them with the real envelopes is what keeps the
 * stories honest: a component that mis-reads `meta.total` or forgets that
 * `cover_url` can be null fails here rather than in production.
 *
 * States that are hard to provoke (an error, an empty catalogue) are still set
 * by overriding the page's own signals; this only supplies the happy path.
 */

const AUTHORS: Author[] = [
  { id: 1, name: 'Gabriel García Márquez', birth_year: 1927, bio: null, books_count: 6 },
  { id: 4, name: 'Jorge Luis Borges', birth_year: 1899, bio: null, books_count: 4 },
  { id: 7, name: 'Roberto Bolaño', birth_year: 1953, bio: null, books_count: 3 },
  { id: 52, name: 'Virginia Woolf', birth_year: 1882, bio: null, books_count: 2 },
];

const CATEGORIES: Category[] = [
  { id: 1, name: 'Fiction', slug: 'fiction', description: 'Novels and short stories.', books_count: 45 },
  { id: 2, name: 'History', slug: 'history', description: 'The record of what happened.', books_count: 9 },
  { id: 3, name: "Children's", slug: 'childrens', description: 'Reading for the earliest ages.', books_count: 7 },
];

const BOOKS: Book[] = [
  {
    id: 1, title: 'One Hundred Years of Solitude', isbn: '9780040000000',
    description: 'The Buendía family across a century in the mythical town of Macondo.',
    publisher: 'Penguin', publication_year: 1967, cover_url: null,
    total_copies: 6, available_copies: 3, is_available: true, active_loans_count: 3,
    authors: [{ id: 1, name: 'Gabriel García Márquez' }],
    categories: [{ id: 1, name: 'Fiction', slug: 'fiction' }],
    matched_on: ['title'], created_at: '2024-09-22T12:36:22Z',
  },
  {
    id: 10, title: 'The Savage Detectives', isbn: '9780040712712',
    description: 'Two poets in search of a vanished writer, told by everyone who met them.',
    publisher: 'Picador', publication_year: 1998, cover_url: null,
    total_copies: 3, available_copies: 0, is_available: false, active_loans_count: 3,
    authors: [{ id: 7, name: 'Roberto Bolaño' }],
    categories: [{ id: 1, name: 'Fiction', slug: 'fiction' }],
    created_at: '2024-02-17T12:36:22Z',
  },
  {
    id: 70, title: 'To the Lighthouse', isbn: '9780045464111',
    description: 'A family, a house by the sea, and ten years passing in a single chapter.',
    publisher: 'Hogarth Press', publication_year: 1927, cover_url: null,
    total_copies: 3, available_copies: 3, is_available: true, active_loans_count: 0,
    authors: [{ id: 52, name: 'Virginia Woolf' }],
    categories: [{ id: 1, name: 'Fiction', slug: 'fiction' }],
    created_at: '2024-02-04T12:36:23Z',
  },
];

const USERS: User[] = [
  { id: 1, name: 'Alicia Ferrer', email: 'admin@librarium.test', roles: ['admin'], permissions: [], is_active: true, active_loans_count: 0, created_at: '2024-01-05T00:00:00Z' },
  { id: 9, name: 'Luis Prado', email: 'librarian@librarium.test', roles: ['librarian'], permissions: [], is_active: true, active_loans_count: 1, created_at: '2024-06-12T00:00:00Z' },
  { id: 34, name: 'Martha Reed', email: 'member@librarium.test', roles: ['member'], permissions: [], is_active: true, active_loans_count: 2, created_at: '2025-01-10T00:00:00Z' },
  { id: 55, name: 'Ivan Costa', email: 'ivan@example.com', roles: ['member'], permissions: [], is_active: false, active_loans_count: 0, created_at: '2025-03-22T00:00:00Z' },
];

const LOANS: Loan[] = [
  {
    id: 501, status: 'overdue', loaned_at: '2026-06-26', due_date: '2026-07-10', returned_at: null, days_overdue: 12,
    user: { id: 34, name: 'Martha Reed', email: 'member@librarium.test' },
    book: { id: 10, title: 'The Savage Detectives', isbn: '9780040712712' },
  },
  {
    id: 502, status: 'active', loaned_at: '2026-07-14', due_date: '2026-07-28', returned_at: null, days_overdue: 0,
    user: { id: 34, name: 'Martha Reed', email: 'member@librarium.test' },
    book: { id: 1, title: 'One Hundred Years of Solitude', isbn: '9780040000000' },
  },
  {
    id: 503, status: 'returned', loaned_at: '2026-06-20', due_date: '2026-07-04', returned_at: '2026-07-01T00:00:00Z', days_overdue: 0,
    user: { id: 55, name: 'Ivan Costa', email: 'ivan@example.com' },
    book: { id: 70, title: 'To the Lighthouse', isbn: '9780045464111' },
  },
];

const KPIS: DashboardKpis = {
  total_books: 81, total_copies: 243, available_copies: 201,
  borrowed_now: 42, overdue_now: 25, total_members: 32,
  books_added_this_month: 4, loans_this_month: 35, returns_this_month: 13,
};

const ACTIVITY: ActivityItem[] = [
  { type: 'loan_created', occurred_at: '2026-07-23T00:00:00Z', summary: 'Beatrice Cannon borrowed "Brave New World"', book: { id: 21, title: 'Brave New World' }, user: { id: 24, name: 'Beatrice Cannon' } },
  { type: 'loan_returned', occurred_at: '2026-07-22T00:00:00Z', summary: 'Hugh Nieto returned "Murder on the Orient Express"', book: { id: 55, title: 'Murder on the Orient Express' }, user: { id: 21, name: 'Hugh Nieto' } },
  { type: 'book_created', occurred_at: '2026-07-21T00:00:00Z', summary: '"A Fine Balance" added to the catalogue', book: { id: 77, title: 'A Fine Balance' }, user: null },
];

const POPULAR_AUTHORS: PopularAuthor[] = [
  { author: { id: 1, name: 'Gabriel García Márquez' }, loans_count: 10 },
  { author: { id: 4, name: 'Jorge Luis Borges' }, loans_count: 6 },
  { author: { id: 7, name: 'Roberto Bolaño' }, loans_count: 5 },
];

const BY_CATEGORY: CategoryCount[] = [
  { category: { id: 1, name: 'Fiction' }, books_count: 45 },
  { category: { id: 8, name: 'Crime' }, books_count: 11 },
  { category: { id: 4, name: 'Science' }, books_count: 10 },
  { category: { id: 2, name: 'History' }, books_count: 9 },
];

function page<T>(rows: T[]) {
  return {
    data: rows,
    meta: { current_page: 1, per_page: 15, total: rows.length, last_page: 1 },
  };
}

function envelope<T>(data: T) {
  return { data };
}

/** Storybook only — never registered by the application itself. */
export const fakeApiInterceptor: HttpInterceptorFn = (req, next) => {
  const path = req.url.replace(/^\//, '');
  const status = req.params.get('status');

  const body =
    path === 'books' ? page(BOOKS)
    : path === 'search/books' ? page(BOOKS.slice(0, 1))
    : /^books\/\d+$/.test(path) ? envelope(BOOKS[0])
    : path === 'authors' ? page(AUTHORS)
    : path === 'categories' ? page(CATEGORIES)
    : path === 'users' ? page(USERS)
    : /^users\/\d+$/.test(path) ? envelope(USERS[2])
    : /^users\/\d+\/loans$/.test(path) ? page(LOANS.filter(l => l.status === status))
    : path === 'loans' ? page(status ? LOANS.filter(l => l.status === status) : LOANS)
    : path === 'dashboard' ? envelope(KPIS)
    : path === 'dashboard/recent-activity' ? envelope(ACTIVITY)
    : path === 'dashboard/popular-authors' ? envelope(POPULAR_AUTHORS)
    : path === 'dashboard/books-by-category' ? envelope(BY_CATEGORY)
    : path === 'dashboard/recent-books' ? envelope(BOOKS)
    : path === 'auth/me' ? envelope(USERS[1])
    : null;

  if (body === null) {
    return throwError(() => new HttpErrorResponse({
      status: 404,
      url: req.url,
      error: { error: { code: 'NOT_FOUND', message: 'No fixture for ' + path, trace_id: 'storybook' } },
    }));
  }

  return of(new HttpResponse({ status: 200, body }));
};
