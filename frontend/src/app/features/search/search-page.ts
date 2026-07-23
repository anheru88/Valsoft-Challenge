import { Component, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { takeUntilDestroyed, toObservable, toSignal } from '@angular/core/rxjs-interop';
import { catchError, map, of, switchMap } from 'rxjs';
import { Book } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { EmptyState } from '../../shared/ui/empty-state';
import { ErrorState } from '../../shared/ui/error-state';
import { Skeleton } from '../../shared/ui/skeleton';
import { AvailabilityBadge } from '../../shared/ui/availability-badge';
import { BooksApiService } from '../books/data/books-api.service';

/** FR-SRCH-3: shorter non-ISBN queries are too broad to answer. */
const MIN_QUERY_LENGTH = 2;

@Component({
  selector: 'lib-search-page',
  standalone: true,
  imports: [RouterLink, PageHeader, EmptyState, ErrorState, Skeleton, AvailabilityBadge],
  templateUrl: './search-page.html',
  styles: [`
    .results { display: grid; gap: var(--sp-3); }
    .title { font-weight: 600; font-size: 15px; color: var(--lib-ink); }
    .title:hover { color: var(--lib-green); }
    .matched { font-size: 11px; color: var(--lib-ink-faint); text-transform: uppercase; letter-spacing: .06em; }
  `],
})
export class SearchPage {
  private readonly route = inject(ActivatedRoute);
  private readonly api = inject(BooksApiService);

  /** The term lives in the URL, so a result page can be shared or reloaded. */
  readonly query = toSignal(
    this.route.queryParamMap.pipe(map(params => (params.get('q') ?? '').trim())),
    { initialValue: '' },
  );

  readonly results = signal<Book[]>([]);
  readonly loading = signal(false);
  readonly error = signal(false);
  readonly total = signal(0);

  private readonly revision = signal(0);
  private readonly trigger = computed(() => ({ q: this.query(), revision: this.revision() }));

  constructor() {
    // `switchMap` drops the answer to a term the reader has already replaced —
    // typing in the top bar re-navigates on every submit.
    toObservable(this.trigger).pipe(
      switchMap(({ q }) => {
        this.error.set(false);

        if (q.length < MIN_QUERY_LENGTH) {
          this.results.set([]);
          this.total.set(0);

          return of(null);
        }

        this.loading.set(true);

        return this.api.search({ q, per_page: 20 }).pipe(
          catchError(() => {
            this.error.set(true);

            return of(null);
          }),
        );
      }),
      takeUntilDestroyed(),
    ).subscribe(page => {
      this.loading.set(false);

      if (page) {
        this.results.set(page.data);
        this.total.set(page.meta.total);
      }
    });
  }

  authorNames(b: Book): string { return b.authors.map(a => a.name).join(', '); }

  /** Why this book matched, when the server says so (API specification 8). */
  matchedOn(b: Book): string { return (b.matched_on ?? []).join(' · '); }

  retry(): void { this.revision.update(n => n + 1); }
}
