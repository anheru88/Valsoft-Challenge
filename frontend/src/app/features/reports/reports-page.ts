import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { Loan, MostBorrowedBook, PageMeta } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { DueStamp } from '../../shared/ui/due-stamp';
import { Skeleton } from '../../shared/ui/skeleton';
import { EmptyState } from '../../shared/ui/empty-state';
import { ErrorState } from '../../shared/ui/error-state';
import { ReportsApiService } from './data/reports-api.service';

const EMPTY_META: PageMeta = { current_page: 1, per_page: 15, total: 0, last_page: 1 };

/**
 * The administrator reports (PRD 2.2, FR-RPT): who is overdue, and what the
 * library lends most. Each report loads and fails on its own — the page never
 * goes dark because one query did.
 */
@Component({
  selector: 'lib-reports-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule,
            MatPaginatorModule, PageHeader, DueStamp, Skeleton, EmptyState, ErrorState],
  templateUrl: './reports-page.html',
  styleUrl: './reports-page.scss',
})
export class ReportsPage {
  private readonly api = inject(ReportsApiService);

  // ── Overdue loans ────────────────────────────────────────────────────────
  readonly overdue = signal<Loan[]>([]);
  readonly overdueMeta = signal<PageMeta>(EMPTY_META);
  readonly overdueLoading = signal(true);
  readonly overdueError = signal(false);

  // ── Most borrowed ────────────────────────────────────────────────────────
  readonly popular = signal<MostBorrowedBook[]>([]);
  readonly popularLoading = signal(true);
  readonly popularError = signal(false);
  readonly from = signal('');
  readonly to = signal('');

  constructor() {
    this.loadOverdue();
    this.loadPopular();
  }

  loadOverdue(page = 1, perPage = this.overdueMeta().per_page): void {
    this.overdueLoading.set(true);
    this.overdueError.set(false);

    this.api.overdue({ page, per_page: perPage, direction: 'desc' }).subscribe({
      next: result => {
        this.overdue.set(result.data);
        this.overdueMeta.set(result.meta);
        this.overdueLoading.set(false);
      },
      error: () => { this.overdueError.set(true); this.overdueLoading.set(false); },
    });
  }

  onOverduePage(event: PageEvent): void {
    this.loadOverdue(event.pageIndex + 1, event.pageSize);
  }

  loadPopular(): void {
    this.popularLoading.set(true);
    this.popularError.set(false);

    this.api.mostBorrowed({ from: this.from() || null, to: this.to() || null, limit: 10 }).subscribe({
      next: ({ data }) => { this.popular.set(data); this.popularLoading.set(false); },
      error: () => { this.popularError.set(true); this.popularLoading.set(false); },
    });
  }

  setFrom(value: string): void { this.from.set(value); }
  setTo(value: string): void { this.to.set(value); }
}
