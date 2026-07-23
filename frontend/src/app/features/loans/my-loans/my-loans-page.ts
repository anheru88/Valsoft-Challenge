import { Component, computed, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatTabsModule } from '@angular/material/tabs';
import { Loan } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { Skeleton } from '../../../shared/ui/skeleton';
import { LoansApiService } from '../data/loans-api.service';

@Component({
  selector: 'lib-my-loans-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatTabsModule, PageHeader, DueStamp, EmptyState, ErrorState, Skeleton],
  templateUrl: './my-loans-page.html',
  styles: [`
    .loan-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--sp-4); padding-top: var(--sp-4); }
    .loan-card.overdue { border-left: 3px solid var(--lib-stamp); }
    .strong { font-weight: 600; color: var(--lib-ink); }
    .small { font-size: 13px; margin: var(--sp-3) 0 0; }
    .hist { margin-top: var(--sp-4); }
  `],
})
export class MyLoansPage {
  private readonly router = inject(Router);
  private readonly api = inject(LoansApiService);

  readonly loading = signal(true);
  readonly error = signal(false);
  readonly loans = signal<Loan[]>([]);

  readonly activeLoans = computed(() => this.loans().filter(l => l.status !== 'returned'));
  readonly history = computed(() => this.loans().filter(l => l.status === 'returned'));

  constructor() {
    this.load();
  }

  /**
   * `GET /loans` is scoped by the server to the signed-in member (FR-LOAN-5),
   * so there is no user id to send — and no way to ask for someone else's.
   * A member is capped at five active loans, so one page holds everything that
   * matters; the history is capped at a sensible page rather than paginated.
   */
  load(): void {
    this.loading.set(true);
    this.error.set(false);

    this.api.list({ sort: 'due_date', direction: 'asc', per_page: 50 }).subscribe({
      next: page => {
        this.loans.set(page.data);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.error.set(true);
      },
    });
  }

  daysLeft(l: Loan): number {
    return Math.max(0, Math.ceil((new Date(l.due_date).getTime() - Date.now()) / 86_400_000));
  }

  goCatalog(): void { this.router.navigate(['/books']); }
}
