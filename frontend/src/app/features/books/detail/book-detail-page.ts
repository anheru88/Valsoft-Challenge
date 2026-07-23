import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AuthStore } from '../../../core/auth.store';
import { Book } from '../../../core/models';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';
import { Skeleton } from '../../../shared/ui/skeleton';
import { ErrorState } from '../../../shared/ui/error-state';
import { InlineAlert } from '../../../shared/ui/inline-alert';

@Component({
  selector: 'lib-book-detail-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule,
            AvailabilityBadge, Skeleton, ErrorState, InlineAlert],
  templateUrl: './book-detail-page.html',
  styleUrl: './book-detail-page.scss',
})
export class BookDetailPage {
  private readonly route = inject(ActivatedRoute);
  private readonly auth = inject(AuthStore);

  readonly isStaff = this.auth.isStaff;
  readonly loading = signal(false);
  readonly error = signal(false);

  // Demo data — replace with GET /api/v1/books/{id}
  readonly book = signal<Book>({
    id: 1, title: 'One Hundred Years of Solitude', isbn: '9780307474728',
    description: 'The Buendía family across a century in the mythical town of Macondo: the novel that founded modern magical realism.',
    publisher: 'Penguin', publication_year: 1967, cover_url: undefined,
    total_copies: 5, available_copies: 3, is_available: true, active_loans_count: 2,
    authors: [{ id: 3, name: 'Gabriel García Márquez' }],
    categories: [{ id: 1, name: 'Fiction', slug: 'fiction' }],
    created_at: '2026-06-01',
  });

  load(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    // TODO API: GET /api/v1/books/{id} (soporta ETag/If-None-Match)
    void id;
  }
}
