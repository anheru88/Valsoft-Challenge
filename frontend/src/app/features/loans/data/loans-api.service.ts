import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Loan, LoanStatus, Paginated } from '../../../core/models';

export interface LoanQuery {
  status?: LoanStatus;
  user_id?: number | null;
  book_id?: number | null;
  overdue?: boolean;
  loaned_from?: string;
  loaned_to?: string;
  sort?: 'loaned_at' | 'due_date' | 'returned_at';
  direction?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface CheckoutPayload {
  user_id: number;
  book_id: number;
  /** `YYYY-MM-DD`; optional, defaults to +14 days on the server. */
  due_date?: string;
}

/**
 * Circulation (API specification 6).
 *
 * `GET /loans` is scoped by the server: staff see every loan, a member sees
 * only their own, whatever the parameters say (FR-LOAN-5).
 */
@Injectable({ providedIn: 'root' })
export class LoansApiService {
  private readonly http = inject(HttpClient);

  list(query: LoanQuery = {}): Observable<Paginated<Loan>> {
    return this.http.get<Paginated<Loan>>('loans', { params: toParams({ ...query }) });
  }

  /** Check-out. Business refusals arrive as 409 with a `LOAN_*` code. */
  checkout(payload: CheckoutPayload): Observable<Envelope<Loan>> {
    return this.http.post<Envelope<Loan>>('loans', payload);
  }

  /** Check-in. 409 LOAN_ALREADY_RETURNED if someone got there first. */
  checkIn(id: number): Observable<Envelope<Loan>> {
    return this.http.post<Envelope<Loan>>(`loans/${id}/return`, {});
  }

  /** A member's loan history, for the desk. */
  forUser(userId: number, query: LoanQuery = {}): Observable<Paginated<Loan>> {
    return this.http.get<Paginated<Loan>>(`users/${userId}/loans`, { params: toParams({ ...query }) });
  }
}
