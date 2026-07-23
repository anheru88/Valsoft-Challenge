import { Component, computed, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { businessMessage } from '../../../core/api/error-message';
import { AuthStore } from '../../../core/auth.store';
import { Book, Category } from '../../../core/models';
import { CategoriesApiService } from '../../categories/data/categories-api.service';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { ConfirmDialog } from '../../../shared/ui/confirm-dialog';
import { BooksApiService } from '../data/books-api.service';
import { BooksStore } from '../data/books.store';

interface BookFilters { q: string; categoryId: number | null; onlyAvailable: boolean; }

@Component({
  selector: 'lib-books-list-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatFormFieldModule,
            MatSelectModule, MatSlideToggleModule, MatPaginatorModule,
            PageHeader, SearchInput, AvailabilityBadge, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [BooksStore],
  templateUrl: './books-list-page.html',
  styleUrl: './books-list-page.scss',
})
export class BooksListPage {
  private readonly auth = inject(AuthStore);
  private readonly router = inject(Router);
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(BooksStore);
  private readonly api = inject(BooksApiService);
  private readonly categoriesApi = inject(CategoriesApiService);

  readonly isStaff = this.auth.isStaff;
  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly books = this.store.books;
  readonly meta = this.store.meta;

  /** A refused delete: shown on the page, because it concerns this request. */
  readonly actionError = signal<string | null>(null);

  /** The query as the filter bar thinks of it. */
  readonly filters = computed<BookFilters>(() => {
    const query = this.store.query();

    return {
      q: query.q ?? '',
      categoryId: query.category_id ?? null,
      onlyAvailable: query.available === true,
    };
  });

  readonly sort = computed(() => ({
    field: this.store.query().sort ?? 'title',
    dir: this.store.query().direction ?? 'asc',
  }));

  readonly hasActiveFilters = computed(() =>
    this.filters().q !== '' || this.filters().categoryId !== null || this.filters().onlyAvailable);
  readonly totalLabel = computed(() => this.meta().total + ' titles in the catalogue');

  /** The whole category list: it fits in one page and drives the filter. */
  readonly categories = signal<Pick<Category, 'id' | 'name'>[]>([]);

  constructor() {
    this.categoriesApi.list({ per_page: 100, sort: 'name' })
      .subscribe({ next: ({ data }) => this.categories.set(data), error: () => this.categories.set([]) });
  }

  authorNames(b: Book): string { return b.authors.map(a => a.name).join(', '); }

  setFilter(patch: Partial<BookFilters>): void {
    this.store.patchQuery({
      ...('q' in patch ? { q: patch.q } : {}),
      ...('categoryId' in patch ? { category_id: patch.categoryId } : {}),
      // `available=false` would ask for the unavailable ones; the toggle means
      // "narrow to what can be borrowed", so off is simply no filter.
      ...('onlyAvailable' in patch ? { available: patch.onlyAvailable ? true : undefined } : {}),
    });
  }

  clearFilters(): void { this.store.clearFilters(); }

  toggleSort(field: string): void { this.store.toggleSort(field); }

  sortIndicator(field: string): string {
    const s = this.sort();
    return s.field !== field ? '' : s.dir === 'asc' ? '↑' : '↓';
  }

  onPage(e: PageEvent): void { this.store.setPage(e.pageIndex + 1, e.pageSize); }

  goNew(): void { this.router.navigate(['/books/new']); }

  confirmDelete(b: Book): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: 'Delete «' + b.title + '»?',
      message: 'The book will be withdrawn from the catalogue. Returned loans keep their history.',
      confirmLabel: 'Delete book', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;

      this.actionError.set(null);
      this.api.remove(b.id).subscribe({
        next: () => {
          this.snack.open('«' + b.title + '» removed from the catalogue', undefined, { duration: 4000 });
          this.store.reload();
        },
        // 409 BOOK_HAS_ACTIVE_LOANS is the expected refusal: the title cannot go
        // while copies are out (BR-BOOK-3).
        error: (err: unknown) => this.actionError.set(businessMessage(err)),
      });
    });
  }

  load(): void { this.store.reload(); }
}
