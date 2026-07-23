import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Loan, MostBorrowedBook, Paginated } from '../../../core/models';

export interface OverdueQuery {
  page?: number;
  per_page?: number;
  direction?: 'asc' | 'desc';
}

export interface MostBorrowedQuery {
  from?: string | null;
  to?: string | null;
  limit?: number;
}

/**
 * The administrator reports (API specification 9). Both endpoints are
 * admin-only; the API is the enforcement, this service just names them.
 *
 * They are kept apart, and fetched apart, for the same reason the dashboard
 * widgets are: one report failing should not blank the other.
 */
@Injectable({ providedIn: 'root' })
export class ReportsApiService {
  private readonly http = inject(HttpClient);

  /** Overdue loans with member contact and `days_overdue`, paginated. */
  overdue(query: OverdueQuery = {}): Observable<Paginated<Loan>> {
    return this.http.get<Paginated<Loan>>('reports/overdue', {
      params: toParams({ page: query.page ?? 1, per_page: query.per_page ?? 15, direction: query.direction ?? 'desc' }),
    });
  }

  /** The most-borrowed titles over an optional window. */
  mostBorrowed(query: MostBorrowedQuery = {}): Observable<Envelope<MostBorrowedBook[]>> {
    return this.http.get<Envelope<MostBorrowedBook[]>>('reports/most-borrowed', {
      params: toParams({ from: query.from ?? null, to: query.to ?? null, limit: query.limit ?? 10 }),
    });
  }
}
