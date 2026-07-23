import { Injectable, computed, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { PagedStore } from '../../../core/api/paged-store';
import { Loan, LoanStatus, Paginated } from '../../../core/models';
import { LoanQuery, LoansApiService } from './loans-api.service';

export type StatusFilter = LoanStatus | 'all';

/** The urgent end of the list first: what is due soonest, or already late. */
const DEFAULT_QUERY: LoanQuery = { sort: 'due_date', direction: 'asc', page: 1, per_page: 15 };

/**
 * Circulation as the desk sees it.
 *
 * The status tabs are a server-side filter, not a slice of a page held in
 * memory: filtering here would count only the rows that happen to be on screen,
 * and "Overdue" would say 2 when the library has 25.
 */
@Injectable()
export class LoansStore extends PagedStore<Loan, LoanQuery> {
  private readonly api = inject(LoansApiService);

  readonly loans = this.items;
  readonly statusFilter = computed<StatusFilter>(() => this.query().status ?? 'all');

  constructor() {
    super();
    this.start({ ...DEFAULT_QUERY });
  }

  protected fetch(query: LoanQuery): Observable<Paginated<Loan>> {
    return this.api.list(query);
  }

  setStatus(status: StatusFilter): void {
    this.patchQuery({ status: status === 'all' ? undefined : status });
  }
}
