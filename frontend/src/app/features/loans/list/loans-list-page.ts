import { Component, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTabsModule } from '@angular/material/tabs';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSnackBar } from '@angular/material/snack-bar';
import { businessMessage } from '../../../core/api/error-message';
import { Loan } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { StatusChip } from '../../../shared/ui/status-chip';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { LoansApiService } from '../data/loans-api.service';
import { LoansStore, StatusFilter } from '../data/loans.store';

@Component({
  selector: 'lib-loans-list-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatButtonModule, MatIconModule, MatTabsModule, MatPaginatorModule,
            PageHeader, DueStamp, StatusChip, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [LoansStore],
  templateUrl: './loans-list-page.html',
  styleUrl: './loans-list-page.scss',
})
export class LoansListPage {
  private readonly router = inject(Router);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(LoansStore);
  private readonly api = inject(LoansApiService);

  readonly tabs: { value: StatusFilter; label: string }[] = [
    { value: 'all', label: 'All' }, { value: 'active', label: 'Active' },
    { value: 'overdue', label: 'Overdue' }, { value: 'returned', label: 'Returned' },
  ];

  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly loans = this.store.loans;
  readonly meta = this.store.meta;
  readonly statusFilter = this.store.statusFilter;

  readonly returningId = signal<number | null>(null);
  readonly actionError = signal<string | null>(null);

  /**
   * The badge counts every late loan in the library, not the late ones on this
   * page — which is why it is its own request rather than a filter over `loans`.
   */
  readonly overdueCount = signal(0);

  constructor() {
    this.countOverdue();
  }

  setStatus(s: StatusFilter): void { this.store.setStatus(s); }

  onPage(e: PageEvent): void { this.store.setPage(e.pageIndex + 1, e.pageSize); }

  goCheckout(): void { this.router.navigate(['/loans/checkout']); }

  /** One-click check-in: the most frequent action at the desk. */
  checkIn(l: Loan): void {
    this.returningId.set(l.id);
    this.actionError.set(null);

    this.api.checkIn(l.id).subscribe({
      next: () => {
        this.returningId.set(null);
        this.snack.open('«' + l.book.title + '» checked in', undefined, { duration: 4000 });
        this.store.reload();
        this.countOverdue();
      },
      error: (err: unknown) => {
        this.returningId.set(null);
        // 409 LOAN_ALREADY_RETURNED means another librarian got there first, so
        // the row on screen is stale: say so and refresh it.
        this.actionError.set(businessMessage(err));
        this.store.reload();
      },
    });
  }

  load(): void {
    this.store.reload();
    this.countOverdue();
  }

  private countOverdue(): void {
    this.api.list({ status: 'overdue', per_page: 1 })
      .subscribe({ next: page => this.overdueCount.set(page.meta.total), error: () => this.overdueCount.set(0) });
  }
}
