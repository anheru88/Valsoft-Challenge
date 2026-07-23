// ── Tipos del API (/api/v1) ────────────────────────────────────────────────
export type Role = 'admin' | 'librarian' | 'member';
export type LoanStatus = 'active' | 'overdue' | 'returned';

export interface User {
  id: number; name: string; email: string; role: Role;
  is_active: boolean; active_loans_count?: number; created_at: string;
}

export interface Author { id: number; name: string; bio?: string; birth_year?: number; books_count?: number; }
export interface Category { id: number; name: string; slug: string; description?: string; books_count?: number; }

export interface Book {
  id: number; title: string; isbn: string; description?: string;
  publisher?: string; publication_year?: number; cover_url?: string;
  total_copies: number; available_copies: number; is_available: boolean;
  authors: Pick<Author, 'id' | 'name'>[];
  categories: Pick<Category, 'id' | 'name' | 'slug'>[];
  active_loans_count?: number; // solo staff
  created_at: string;
}

export interface Loan {
  id: number; status: LoanStatus;
  loaned_at: string; due_date: string; returned_at: string | null; days_overdue: number;
  user: Pick<User, 'id' | 'name' | 'email'>;
  book: Pick<Book, 'id' | 'title' | 'isbn'>;
}

export interface Paginated<T> {
  data: T[];
  meta: { current_page: number; per_page: number; total: number; last_page: number };
}

export interface ApiError {
  error: { code: string; message: string; details?: Record<string, unknown>; trace_id: string };
}

export interface DashboardKpis {
  total_books: number; total_copies: number; available_copies: number;
  borrowed_now: number; overdue_now: number; total_members: number;
  books_added_this_month: number; loans_this_month: number; returns_this_month: number;
}
