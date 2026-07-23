import { Component, inject } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { PageHeader } from '../../shared/ui/page-header';
import { StatCard } from '../../shared/ui/stat-card';
import { Skeleton } from '../../shared/ui/skeleton';
import { ErrorState } from '../../shared/ui/error-state';
import { EmptyState } from '../../shared/ui/empty-state';
import { DashboardStore } from './data/dashboard.store';

@Component({
  selector: 'lib-dashboard-page',
  standalone: true,
  imports: [RouterLink, DatePipe, MatButtonModule, MatIconModule,
            PageHeader, StatCard, Skeleton, ErrorState, EmptyState],
  providers: [DashboardStore],
  templateUrl: './dashboard-page.html',
  styleUrl: './dashboard-page.scss',
})
export class DashboardPage {
  private readonly store = inject(DashboardStore);

  readonly loadingKpis = this.store.loadingKpis;
  readonly kpisError = this.store.kpisError;
  readonly kpis = this.store.kpis;
  readonly activity = this.store.activity;
  readonly byCategory = this.store.byCategory;
  readonly popularAuthors = this.store.popularAuthors;
  readonly recentBooks = this.store.recentBooks;
  readonly maxCategoryCount = this.store.maxCategoryCount;

  loadKpis(): void { this.store.loadKpis(); }
}
