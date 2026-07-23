import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Author, Paginated } from '../../../core/models';

export interface AuthorQuery {
  q?: string;
  sort?: 'name' | 'created_at' | 'books_count';
  direction?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface AuthorPayload {
  name: string;
  bio?: string | null;
  birth_year?: number | null;
}

/** The author endpoints (API specification 4). */
@Injectable({ providedIn: 'root' })
export class AuthorsApiService {
  private readonly http = inject(HttpClient);

  list(query: AuthorQuery = {}): Observable<Paginated<Author>> {
    return this.http.get<Paginated<Author>>('authors', { params: toParams({ ...query }) });
  }

  create(payload: AuthorPayload): Observable<Envelope<Author>> {
    return this.http.post<Envelope<Author>>('authors', payload);
  }

  update(id: number, payload: AuthorPayload): Observable<Envelope<Author>> {
    return this.http.put<Envelope<Author>>(`authors/${id}`, payload);
  }

  /** Refused with 409 AUTHOR_IN_USE while books still point at the author. */
  remove(id: number): Observable<void> {
    return this.http.delete<void>(`authors/${id}`);
  }
}
