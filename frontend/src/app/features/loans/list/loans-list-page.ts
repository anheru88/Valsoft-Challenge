import { Component, computed, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTabsModule } from '@angular/material/tabs';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Loan, LoanStatus } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { StatusChip } from '../../../shared/ui/status-chip';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';

type StatusFilter = LoanStatus | 'all';

@Component({
  selector: 'lib-loans-list-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatButtonModule, MatIconModule, MatTabsModule, MatPaginatorModule,
            PageHeader, SearchInput, DueStamp, StatusChip, Skeleton, EmptyState, ErrorState],
  templateUrl: './loans-list-page.html',
  styleUrl: './loans-list-page.scss',
})
export class LoansListPage {
  private readonly router = inject(Router);
  private readonly snack = inject(MatSnackBar);

  readonly tabs: { value: StatusFilter; label: string }[] = [
    { value: 'all', label: 'All' }, { value: 'active', label: 'Active' },
    { value: 'overdue', label: 'Overdue' }, { value: 'returned', label: 'Returned' },
  ];

  readonly loading = signal(false);
  readonly error = signal(false);
  readonly statusFilter = signal<StatusFilter>('all');
  readonly returningId = signal<number | null>(null);
  readonly meta = signal({ current_page: 1, per_page: 15, total: 3, last_page: 1 });

  // Demo data — GET /api/v1/loans?status&q&page (due_date ascending by default: the urgent ones first)
  readonly allLoans = signal<Loan[]>([
    { id: 501, status: 'overdue', loaned_at: '2026-06-26', due_date: '2026-07-10', returned_at: null, days_overdue: 12,
      user: { id: 21, name: 'Lucía Gómez', email: 'lucia@example.com' },
      book: { id: 2, title: 'El infinito en un junco', isbn: '9788417860790' } },
    { id: 502, status: 'active', loaned_at: '2026-07-14', due_date: '2026-07-28', returned_at: null, days_overdue: 0,
      user: { id: 34, name: 'Marta Ruiz', email: 'marta@example.com' },
      book: { id: 1, title: 'One Hundred Years of Solitude', isbn: '9780307474728' } },
    { id: 503, status: 'returned', loaned_at: '2026-06-20', due_date: '2026-07-04', returned_at: '2026-07-01', days_overdue: 0,
      user: { id: 40, name: 'Jorge Peña', email: 'jorge@example.com' },
      book: { id: 3, title: 'Matilda', isbn: '9788420482880' } },
  ]);

  readonly loans = computed(() => {
    const f = this.statusFilter();
    return f === 'all' ? this.allLoans() : this.allLoans().filter(l => l.status === f);
  });
  readonly overdueCount = computed(() => this.allLoans().filter(l => l.status === 'overdue').length);

  setStatus(s: StatusFilter): void { this.statusFilter.set(s); this.load(); }
  onSearch(q: string): void { void q; this.load(); }
  onPage(e: PageEvent): void {
    this.meta.update(m => ({ ...m, current_page: e.pageIndex + 1, per_page: e.pageSize }));
    this.load();
  }
  goCheckout(): void { this.router.navigate(['/loans/checkout']); }

  /** One-click check-in: the most frequent action at the desk. */
  checkIn(l: Loan): void {
    this.returningId.set(l.id);
    // TODO API: POST /api/v1/loans/{id}/return
    //  200 → toast and refresh; 409 LOAN_ALREADY_RETURNED → refresh the row (another librarian got there first).
    this.snack.open('«' + l.book.title + '» devuelto', undefined, { duration: 4000 });
    this.returningId.set(null);
  }

  load(): void { /* TODO API */ }
}
