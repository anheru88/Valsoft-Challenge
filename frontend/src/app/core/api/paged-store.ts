import { computed, signal } from '@angular/core';
import { takeUntilDestroyed, toObservable } from '@angular/core/rxjs-interop';
import { Observable, catchError, of, switchMap, tap } from 'rxjs';
import { PageMeta, Paginated } from '../models';

const EMPTY_META: PageMeta = { current_page: 1, per_page: 15, total: 0, last_page: 1 };

/**
 * The machinery every paginated list screen repeats: a query, a request per
 * change of it, and the three states the PRD demands of a list (loading,
 * loaded, failed).
 *
 * RxJS lives here and nowhere above it (RFC 15.2). It earns its place with
 * `switchMap`: typing in a filter fires a request per keystroke's worth of
 * debounce, and the answer to a query the user has already moved past must be
 * dropped rather than painted over the current one.
 */
export abstract class PagedStore<T, Q extends object> {
  private readonly _query = signal<Q>({} as Q);
  private readonly _revision = signal(0);

  readonly query = this._query.asReadonly();
  readonly items = signal<T[]>([]);
  readonly meta = signal<PageMeta>(EMPTY_META);
  readonly loading = signal(true);
  readonly error = signal(false);

  /** True when the list came back empty because there is nothing to show. */
  readonly isEmpty = computed(() => !this.loading() && !this.error() && this.items().length === 0);

  private readonly trigger = computed(() => ({ query: this._query(), revision: this._revision() }));

  protected abstract fetch(query: Q): Observable<Paginated<T>>;

  /**
   * Subclasses call this once they are constructed, so their own injected
   * dependencies exist by the time the first request goes out.
   */
  protected start(initial: Q): void {
    this._query.set(initial);

    toObservable(this.trigger)
      .pipe(
        tap(() => {
          this.loading.set(true);
          this.error.set(false);
        }),
        switchMap(({ query }) =>
          this.fetch(query).pipe(
            catchError(() => {
              this.error.set(true);

              return of(null);
            }),
          ),
        ),
        takeUntilDestroyed(),
      )
      .subscribe(page => {
        this.loading.set(false);

        if (page) {
          this.items.set(page.data);
          this.meta.set(page.meta);
        }
      });
  }

  /** Narrows the list. Any change of filter sends the reader back to page 1. */
  patchQuery(patch: Partial<Q>): void {
    this._query.update(current => ({ ...current, ...patch, page: 1 }));
  }

  /** Moves within the current result set, leaving the filters alone. */
  setPage(page: number, perPage: number): void {
    this._query.update(current => ({ ...current, page, per_page: perPage }));
  }

  setQuery(query: Q): void {
    this._query.set(query);
  }

  /** Re-runs the current query — after a write, or after a failed attempt. */
  reload(): void {
    this._revision.update(n => n + 1);
  }
}
