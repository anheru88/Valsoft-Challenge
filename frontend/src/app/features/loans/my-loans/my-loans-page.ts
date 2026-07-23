import { Component, computed, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatTabsModule } from '@angular/material/tabs';
import { Loan } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { EmptyState } from '../../../shared/ui/empty-state';

@Component({
  selector: 'lib-my-loans-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatTabsModule, PageHeader, DueStamp, EmptyState],
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

  // TODO API: GET /api/v1/loans — el API lo limita al socio autenticado
  // (FR-LOAN-5), and ?status=returned gives the history. Demo data for now:
  readonly loans = signal<Loan[]>([
    { id: 601, status: 'active', loaned_at: '2026-07-14', due_date: '2026-07-28', returned_at: null, days_overdue: 0,
      user: { id: 34, name: 'Marta Ruiz', email: 'marta@example.com' },
      book: { id: 1, title: 'One Hundred Years of Solitude', isbn: '9780307474728' } },
    { id: 600, status: 'overdue', loaned_at: '2026-06-30', due_date: '2026-07-14', returned_at: null, days_overdue: 8,
      user: { id: 34, name: 'Marta Ruiz', email: 'marta@example.com' },
      book: { id: 7, title: 'La ciudad y sus muros inciertos', isbn: '9788411074278' } },
    { id: 590, status: 'returned', loaned_at: '2026-05-02', due_date: '2026-05-16', returned_at: '2026-05-12', days_overdue: 0,
      user: { id: 34, name: 'Marta Ruiz', email: 'marta@example.com' },
      book: { id: 3, title: 'Matilda', isbn: '9788420482880' } },
  ]);

  readonly activeLoans = computed(() => this.loans().filter(l => l.status !== 'returned'));
  readonly history = computed(() => this.loans().filter(l => l.status === 'returned'));

  daysLeft(l: Loan): number {
    return Math.max(0, Math.ceil((new Date(l.due_date).getTime() - Date.now()) / 86_400_000));
  }
  goCatalog(): void { this.router.navigate(['/books']); }
}
