import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import {
  ActivityItem, Book, CategoryCount, DashboardKpis, MonthlyStats, PopularAuthor,
} from '../../../core/models';

/**
 * The dashboard widgets (API specification 9).
 *
 * Each one is its own endpoint on purpose: a widget that fails should leave the
 * rest of the panel standing, which it can only do if it was fetched apart.
 */
@Injectable({ providedIn: 'root' })
export class DashboardApiService {
  private readonly http = inject(HttpClient);

  kpis(): Observable<Envelope<DashboardKpis>> {
    return this.http.get<Envelope<DashboardKpis>>('dashboard');
  }

  recentActivity(limit = 8): Observable<Envelope<ActivityItem[]>> {
    return this.http.get<Envelope<ActivityItem[]>>('dashboard/recent-activity', { params: toParams({ limit }) });
  }

  popularAuthors(limit = 5, window = '30d'): Observable<Envelope<PopularAuthor[]>> {
    return this.http.get<Envelope<PopularAuthor[]>>('dashboard/popular-authors', { params: toParams({ limit, window }) });
  }

  recentBooks(limit = 5): Observable<Envelope<Book[]>> {
    return this.http.get<Envelope<Book[]>>('dashboard/recent-books', { params: toParams({ limit }) });
  }

  booksByCategory(): Observable<Envelope<CategoryCount[]>> {
    return this.http.get<Envelope<CategoryCount[]>>('dashboard/books-by-category');
  }

  monthlyStats(months = 12): Observable<Envelope<MonthlyStats>> {
    return this.http.get<Envelope<MonthlyStats>>('dashboard/monthly-stats', { params: toParams({ months }) });
  }
}
