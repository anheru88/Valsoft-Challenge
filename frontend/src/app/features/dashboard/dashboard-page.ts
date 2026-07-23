import { Component, computed, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { DashboardKpis } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { StatCard } from '../../shared/ui/stat-card';
import { Skeleton } from '../../shared/ui/skeleton';
import { ErrorState } from '../../shared/ui/error-state';
import { EmptyState } from '../../shared/ui/empty-state';

@Component({
  selector: 'lib-dashboard-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatButtonModule, MatIconModule,
            PageHeader, StatCard, Skeleton, ErrorState, EmptyState],
  templateUrl: './dashboard-page.html',
  styleUrl: './dashboard-page.scss',
})
export class DashboardPage {
  readonly loadingKpis = signal(false);
  readonly kpisError = signal(false);

  // Demo data — replace with GET /api/v1/dashboard and its companion widgets
  readonly kpis = signal<DashboardKpis>({
    total_books: 1240, total_copies: 3480, available_copies: 2960,
    borrowed_now: 520, overdue_now: 34, total_members: 812,
    books_added_this_month: 18, loans_this_month: 210, returns_this_month: 195,
  });

  readonly activity = signal([
    { id: 1, type: 'loan_created',  summary: 'Marta Ruiz borrowed «The Name of the Wind»', occurred_at: '2026-07-22T10:24:00Z' },
    { id: 2, type: 'loan_returned', summary: 'Jorge Peña returned «Sapiens»',              occurred_at: '2026-07-22T09:58:00Z' },
    { id: 3, type: 'book_created',  summary: 'Alta de «La ciudad y sus muros inciertos»',  occurred_at: '2026-07-21T17:31:00Z' },
    { id: 4, type: 'loan_created',  summary: 'Lucía Gómez borrowed «One Hundred Years of Solitude»', occurred_at: '2026-07-21T12:05:00Z' },
  ]);

  readonly byCategory = signal([
    { name: 'Fiction',   count: 412 }, { name: 'Historia', count: 268 },
    { name: 'Infantil',    count: 231 }, { name: 'Ciencia',  count: 175 },
    { name: 'Poetry',      count: 96 },
  ]);
  readonly maxCategoryCount = computed(() => Math.max(...this.byCategory().map(c => c.count), 1));

  readonly popularAuthors = signal([
    { name: 'Gabriel García Márquez', loans: 42 }, { name: 'Isabel Allende', loans: 37 },
    { name: 'Haruki Murakami', loans: 31 }, { name: 'Yuval Noah Harari', loans: 24 },
    { name: 'Ana María Matute', loans: 19 },
  ]);

  readonly recentBooks = signal([
    { id: 7,  title: 'La ciudad y sus muros inciertos', isbn: '9788411074278' },
    { id: 12, title: 'El infinito en un junco',          isbn: '9788417860790' },
    { id: 15, title: 'Klara y el Sol',                   isbn: '9788433980700' },
  ]);

  loadKpis(): void {
    // TODO API: GET /api/v1/dashboard (+ /recent-activity, /books-by-category,
    // /popular-authors?window=30d, /recent-books). Cada widget con su propio estado.
  }
}
