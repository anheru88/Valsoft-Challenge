import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Book, Paginated } from '../../../core/models';

/** The filters `GET /books` accepts (API specification 3). */
export interface BookQuery {
  q?: string;
  category_id?: number | null;
  author_id?: number | null;
  available?: boolean;
  year_from?: number | null;
  year_to?: number | null;
  sort?: string;
  direction?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

/** The create/update body; `available_copies` is system-managed and not sent. */
export interface BookPayload {
  title: string;
  isbn: string;
  description?: string | null;
  publisher?: string | null;
  publication_year?: number | null;
  cover_url?: string | null;
  total_copies: number;
  author_ids: number[];
  category_ids: number[];
}

/**
 * The catalogue endpoints (API specification 3).
 *
 * Paths are relative: `apiBaseInterceptor` resolves them against the configured
 * server, so this service says nothing about where the API lives.
 */
@Injectable({ providedIn: 'root' })
export class BooksApiService {
  private readonly http = inject(HttpClient);

  list(query: BookQuery = {}): Observable<Paginated<Book>> {
    return this.http.get<Paginated<Book>>('books', { params: toParams({ ...query }) });
  }

  /** Relevance-ordered search; falls back to the same envelope as the list. */
  search(query: BookQuery & { q: string }): Observable<Paginated<Book>> {
    return this.http.get<Paginated<Book>>('search/books', { params: toParams({ ...query }) });
  }

  get(id: number): Observable<Envelope<Book>> {
    return this.http.get<Envelope<Book>>(`books/${id}`);
  }

  create(payload: BookPayload): Observable<Envelope<Book>> {
    return this.http.post<Envelope<Book>>('books', payload);
  }

  update(id: number, payload: BookPayload): Observable<Envelope<Book>> {
    return this.http.put<Envelope<Book>>(`books/${id}`, payload);
  }

  /** Soft delete; refused with 409 BOOK_HAS_ACTIVE_LOANS while copies are out. */
  remove(id: number): Observable<void> {
    return this.http.delete<void>(`books/${id}`);
  }
}
