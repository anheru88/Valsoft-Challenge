import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Category, Paginated } from '../../../core/models';

export interface CategoryQuery {
  q?: string;
  sort?: 'name' | 'books_count';
  direction?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface CategoryPayload {
  name: string;
  description?: string | null;
}

/** The category endpoints (API specification 5); the slug is server-generated. */
@Injectable({ providedIn: 'root' })
export class CategoriesApiService {
  private readonly http = inject(HttpClient);

  list(query: CategoryQuery = {}): Observable<Paginated<Category>> {
    return this.http.get<Paginated<Category>>('categories', { params: toParams({ ...query }) });
  }

  create(payload: CategoryPayload): Observable<Envelope<Category>> {
    return this.http.post<Envelope<Category>>('categories', payload);
  }

  update(id: number, payload: CategoryPayload): Observable<Envelope<Category>> {
    return this.http.put<Envelope<Category>>(`categories/${id}`, payload);
  }

  /** Refused with 409 CATEGORY_IN_USE while books are filed under it. */
  remove(id: number): Observable<void> {
    return this.http.delete<void>(`categories/${id}`);
  }
}
