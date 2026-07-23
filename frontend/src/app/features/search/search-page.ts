import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Book } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { EmptyState } from '../../shared/ui/empty-state';
import { AvailabilityBadge } from '../../shared/ui/availability-badge';

@Component({
  selector: 'lib-search-page',
  standalone: true,
  imports: [RouterLink, PageHeader, EmptyState, AvailabilityBadge],
  templateUrl: './search-page.html',
  styles: [`
    .results { display: grid; gap: var(--sp-3); }
    .title { font-weight: 600; font-size: 15px; color: var(--lib-ink); }
    .title:hover { color: var(--lib-green); }
  `],
})
export class SearchPage {
  private readonly route = inject(ActivatedRoute);

  readonly query = signal('');
  readonly results = signal<Book[]>([]);

  constructor() {
    this.route.queryParamMap.pipe(takeUntilDestroyed()).subscribe(params => {
      const q = params.get('q') ?? '';
      this.query.set(q);
      this.search(q);
    });
  }

  authorNames(b: Book): string { return b.authors.map(a => a.name).join(', '); }

  private search(q: string): void {
    if (q.length < 2) { this.results.set([]); return; }
    // TODO API: GET /api/v1/books?q= — de momento, demostración:
    this.results.set([
      { id: 1, title: 'Cien años de soledad', isbn: '9780307474728', total_copies: 5, available_copies: 3,
        is_available: true, authors: [{ id: 3, name: 'Gabriel García Márquez' }],
        categories: [{ id: 1, name: 'Narrativa', slug: 'narrativa' }], created_at: '' },
    ]);
  }
}
