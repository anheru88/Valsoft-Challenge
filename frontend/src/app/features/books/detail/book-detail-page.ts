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
import { BooksApiService } from '../data/books-api.service';

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
  private readonly api = inject(BooksApiService);

  readonly isStaff = this.auth.isStaff;
  readonly loading = signal(true);
  readonly error = signal(false);
  readonly book = signal<Book | null>(null);

  constructor() {
    this.load();
  }

  /**
   * The endpoint carries an ETag; the browser replays `If-None-Match` on its
   * own, so a revisit that has not changed costs a 304 and no payload.
   */
  load(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    if (!id) {
      this.loading.set(false);
      this.error.set(true);

      return;
    }

    this.loading.set(true);
    this.error.set(false);

    this.api.get(id).subscribe({
      next: ({ data }) => {
        this.book.set(data);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.error.set(true);
      },
    });
  }
}
