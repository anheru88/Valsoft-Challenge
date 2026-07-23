import { Component, computed, effect, inject, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { map } from 'rxjs';
import { businessMessage } from '../../../core/api/error-message';
import { AuthStore } from '../../../core/auth.store';
import { Author, Book, Category } from '../../../core/models';
import { AuthorsApiService } from '../../authors/data/authors-api.service';
import { CategoriesApiService } from '../../categories/data/categories-api.service';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { FilterBar } from '../../../shared/ui/filter-bar';
import { CellTemplate, ColumnDef, DataTable } from '../../../shared/ui/data-table';
import { EntityAutocomplete } from '../../../shared/ui/entity-autocomplete';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';
import { BookCover } from '../../../shared/ui/book-cover';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { ConfirmDialog } from '../../../shared/ui/confirm-dialog';
import { BooksApiService } from '../data/books-api.service';
import { BooksStore, DEFAULT_BOOK_QUERY } from '../data/books.store';
import { bookQueryFromParams, bookQueryToParams } from '../data/book-query-params';

const COLUMNS: ColumnDef[] = [
  { key: 'title', header: 'Title', sortable: true, primary: true },
  { key: 'authors', header: 'Authors' },
  { key: 'categories', header: 'Categories', secondary: true },
  { key: 'isbn', header: 'ISBN' },
  { key: 'availability', header: 'Availability', sortable: true },
  { key: 'actions', header: 'Actions', bare: true },
];

@Component({
  selector: 'lib-books-list-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatFormFieldModule,
            MatInputModule, MatSelectModule, MatSlideToggleModule,
            PageHeader, SearchInput, FilterBar, DataTable, CellTemplate, EntityAutocomplete,
            AvailabilityBadge, BookCover, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [BooksStore],
  templateUrl: './books-list-page.html',
  styleUrl: './books-list-page.scss',
})
export class BooksListPage {
  private readonly auth = inject(AuthStore);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(BooksStore);
  private readonly api = inject(BooksApiService);
  private readonly authorsApi = inject(AuthorsApiService);
  private readonly categoriesApi = inject(CategoriesApiService);

  readonly columns = COLUMNS;
  readonly currentYear = new Date().getFullYear();

  readonly isStaff = this.auth.isStaff;
  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly books = this.store.books;
  readonly meta = this.store.meta;

  /** A refused delete: shown on the page, because it concerns this request. */
  readonly actionError = signal<string | null>(null);
  /** The book the refusal was about, so the reader can go and see its loans. */
  readonly blockedBook = signal<Book | null>(null);

  readonly filters = computed(() => {
    const query = this.store.query();

    return {
      q: query.q ?? '',
      categoryId: query.category_id ?? null,
      onlyAvailable: query.available === true,
      yearFrom: query.year_from ?? null,
      yearTo: query.year_to ?? null,
    };
  });

  readonly sort = computed(() => ({
    field: this.store.query().sort ?? 'title',
    direction: this.store.query().direction ?? 'asc',
  }));

  readonly hasActiveFilters = this.store.hasActiveFilters;
  readonly totalLabel = computed(() => this.meta().total + ' titles in the catalogue');

  /** The whole category list: it fits in one page and drives the filter. */
  readonly categories = signal<Pick<Category, 'id' | 'name'>[]>([]);
  /** Sixty-odd authors is too many for a select, so this one is searched. */
  readonly author = signal<Pick<Author, 'id' | 'name'> | null>(null);

  readonly searchAuthors = (term: string) =>
    this.authorsApi.list({ q: term, per_page: 8, sort: 'name' }).pipe(map(page => page.data));
  readonly authorName = (a: Pick<Author, 'id' | 'name'>) => a.name;

  constructor() {
    // The URL is where the query really lives, so a filtered catalogue can be
    // shared or bookmarked, and the category chips on a book's detail page are
    // ordinary links (PRD 4.2).
    this.store.setQuery(bookQueryFromParams(this.route.snapshot.queryParamMap, DEFAULT_BOOK_QUERY));

    effect(() => {
      this.router.navigate([], {
        relativeTo: this.route,
        queryParams: bookQueryToParams(this.store.query(), DEFAULT_BOOK_QUERY),
        replaceUrl: true,
      });
    });

    this.categoriesApi.list({ per_page: 100, sort: 'name' })
      .subscribe({ next: ({ data }) => this.categories.set(data), error: () => this.categories.set([]) });

    // An author filter arriving in the URL has a name to show, not just an id.
    const authorId = this.store.query().author_id;
    if (authorId) {
      this.authorsApi.list({ per_page: 100, sort: 'name' })
        .subscribe({ next: ({ data }) => this.author.set(data.find(a => a.id === authorId) ?? null) });
    }
  }

  readonly trackById = (b: Book) => b.id;

  authorNames(b: Book): string { return b.authors.map(a => a.name).join(', '); }

  setQ(q: string): void { this.store.patchQuery({ q }); }
  setCategory(categoryId: number | null): void { this.store.patchQuery({ category_id: categoryId }); }

  /**
   * `available=false` would ask for the unavailable ones; the toggle means
   * "narrow to what can be borrowed", so off is simply no filter.
   */
  setAvailable(only: boolean): void { this.store.patchQuery({ available: only ? true : undefined }); }

  setAuthor(author: Pick<Author, 'id' | 'name'> | null): void {
    this.author.set(author);
    this.store.patchQuery({ author_id: author?.id ?? null });
  }

  setYear(bound: 'year_from' | 'year_to', raw: string): void {
    const year = Number(raw);
    this.store.patchQuery({ [bound]: raw && Number.isFinite(year) ? year : null });
  }

  clearFilters(): void {
    this.author.set(null);
    this.store.clearFilters();
  }

  toggleSort(field: string): void { this.store.toggleSort(field); }

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
      this.blockedBook.set(null);

      this.api.remove(b.id).subscribe({
        next: () => {
          this.snack.open('«' + b.title + '» removed from the catalogue', undefined, { duration: 4000 });
          this.store.reload();
        },
        // 409 BOOK_HAS_ACTIVE_LOANS is the expected refusal: the title cannot go
        // while copies are out (BR-BOOK-3). The alert links to those loans,
        // because checking them in is what unblocks the deletion.
        error: (err: unknown) => {
          this.actionError.set(businessMessage(err));
          this.blockedBook.set(b);
        },
      });
    });
  }

  load(): void { this.store.reload(); }
}
