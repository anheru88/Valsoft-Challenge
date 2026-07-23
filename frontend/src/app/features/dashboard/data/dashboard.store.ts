import { Injectable, computed, inject, signal } from '@angular/core';
import {
  ActivityItem, Book, CategoryCount, DashboardKpis, PopularAuthor,
} from '../../../core/models';
import { DashboardApiService } from './dashboard-api.service';

/**
 * The dashboard, widget by widget.
 *
 * Each panel keeps its own state on purpose: a dashboard is a summary, and a
 * summary that goes blank because one of its five queries timed out is worse
 * than a summary with a hole in it. The KPIs are the only part with a visible
 * failure state — the rest simply stay empty.
 */
@Injectable()
export class DashboardStore {
  private readonly api = inject(DashboardApiService);

  readonly kpis = signal<DashboardKpis | null>(null);
  readonly loadingKpis = signal(true);
  readonly kpisError = signal(false);

  readonly activity = signal<ActivityItem[]>([]);
  readonly byCategory = signal<CategoryCount[]>([]);
  readonly popularAuthors = signal<PopularAuthor[]>([]);
  readonly recentBooks = signal<Book[]>([]);

  /** The longest bar sets the scale; an empty panel must not divide by zero. */
  readonly maxCategoryCount = computed(() =>
    Math.max(...this.byCategory().map(c => c.books_count), 1));

  constructor() {
    this.loadKpis();
    this.loadWidgets();
  }

  loadKpis(): void {
    this.loadingKpis.set(true);
    this.kpisError.set(false);

    this.api.kpis().subscribe({
      next: ({ data }) => {
        this.kpis.set(data);
        this.loadingKpis.set(false);
      },
      error: () => {
        this.loadingKpis.set(false);
        this.kpisError.set(true);
      },
    });
  }

  private loadWidgets(): void {
    this.api.recentActivity(8).subscribe({ next: ({ data }) => this.activity.set(data) });
    this.api.booksByCategory().subscribe({ next: ({ data }) => this.byCategory.set(data) });
    this.api.popularAuthors(5).subscribe({ next: ({ data }) => this.popularAuthors.set(data) });
    this.api.recentBooks(5).subscribe({ next: ({ data }) => this.recentBooks.set(data) });
  }
}
