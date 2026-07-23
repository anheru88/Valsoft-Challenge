import { Injectable, computed, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { PagedStore } from '../../../core/api/paged-store';
import { Book, Paginated } from '../../../core/models';
import { BookQuery, BooksApiService } from './books-api.service';

export const DEFAULT_BOOK_QUERY: BookQuery = { sort: 'title', direction: 'asc', page: 1, per_page: 15 };

/**
 * The catalogue list (RFC 15.2: a store per feature, signals out, RxJS in).
 *
 * Provided by the page rather than in root, so leaving the screen throws the
 * filters away instead of surprising the next visit with someone else's search.
 */
@Injectable()
export class BooksStore extends PagedStore<Book, BookQuery> {
  private readonly api = inject(BooksApiService);

  readonly books = this.items;

  readonly hasActiveFilters = computed(() => {
    const query = this.query();

    return !!query.q
      || query.category_id != null
      || query.author_id != null
      || query.year_from != null
      || query.year_to != null
      || query.available === true;
  });

  constructor() {
    super();
    this.start({ ...DEFAULT_BOOK_QUERY });
  }

  protected fetch(query: BookQuery): Observable<Paginated<Book>> {
    return this.api.list(query);
  }

  clearFilters(): void {
    this.setQuery({ ...DEFAULT_BOOK_QUERY, per_page: this.query().per_page });
  }

  /** Sorting on a column already sorted flips its direction. */
  toggleSort(field: string): void {
    const { sort, direction } = this.query();

    this.patchQuery(sort === field
      ? { sort: field, direction: direction === 'asc' ? 'desc' : 'asc' }
      : { sort: field, direction: 'asc' });
  }
}
