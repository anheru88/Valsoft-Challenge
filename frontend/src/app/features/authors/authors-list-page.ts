import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { businessMessage } from '../../core/api/error-message';
import { Author } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { SearchInput } from '../../shared/ui/search-input';
import { InlineAlert } from '../../shared/ui/inline-alert';
import { Skeleton } from '../../shared/ui/skeleton';
import { EmptyState } from '../../shared/ui/empty-state';
import { ErrorState } from '../../shared/ui/error-state';
import { ConfirmDialog } from '../../shared/ui/confirm-dialog';
import { AuthorFormDialog } from './author-form-dialog';
import { AuthorsApiService } from './data/authors-api.service';
import { AuthorsStore } from './data/authors.store';

@Component({
  selector: 'lib-authors-list-page',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, MatPaginatorModule,
            PageHeader, SearchInput, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [AuthorsStore],
  templateUrl: './authors-list-page.html',
  styles: [`
    .strong { font-weight: 600; } .num { text-align: right; } .actions-col { width: 96px; text-align: right; }
    mat-paginator { border-top: 1px solid var(--lib-line); }
  `],
})
export class AuthorsListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(AuthorsStore);
  private readonly api = inject(AuthorsApiService);

  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly authors = this.store.authors;
  readonly meta = this.store.meta;
  readonly actionError = signal<string | null>(null);

  onSearch(q: string): void { this.store.patchQuery({ q }); }

  onPage(e: PageEvent): void { this.store.setPage(e.pageIndex + 1, e.pageSize); }

  load(): void { this.store.reload(); }

  /** Small enough to edit in place; a whole page for three fields would not pay. */
  openForm(author?: Author): void {
    this.dialog.open(AuthorFormDialog, { width: '480px', data: author ?? null })
      .afterClosed().subscribe(saved => {
        if (!saved) return;

        this.snack.open(author ? 'Author updated' : 'Author created', undefined, { duration: 4000 });
        this.store.reload();
      });
  }

  confirmDelete(a: Author): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: 'Delete ' + a.name + '?',
      message: (a.books_count ?? 0) > 0
        ? 'This author is attached to ' + a.books_count + ' books, which have to be reassigned first.'
        : 'The author will be removed from the catalogue.',
      confirmLabel: 'Delete author', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;

      this.actionError.set(null);
      this.api.remove(a.id).subscribe({
        next: () => {
          this.snack.open('Author deleted', undefined, { duration: 4000 });
          this.store.reload();
        },
        // 409 AUTHOR_IN_USE: books still point at them (BR-AUTH-1).
        error: (err: unknown) => this.actionError.set(businessMessage(err)),
      });
    });
  }
}
