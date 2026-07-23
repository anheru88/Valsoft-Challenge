import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { businessMessage } from '../../core/api/error-message';
import { Category } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { SearchInput } from '../../shared/ui/search-input';
import { InlineAlert } from '../../shared/ui/inline-alert';
import { Skeleton } from '../../shared/ui/skeleton';
import { EmptyState } from '../../shared/ui/empty-state';
import { ErrorState } from '../../shared/ui/error-state';
import { ConfirmDialog } from '../../shared/ui/confirm-dialog';
import { CategoryFormDialog } from './category-form-dialog';
import { CategoriesApiService } from './data/categories-api.service';
import { CategoriesStore } from './data/categories.store';

@Component({
  selector: 'lib-categories-list-page',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, MatPaginatorModule,
            PageHeader, SearchInput, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [CategoriesStore],
  templateUrl: './categories-list-page.html',
  styles: [`
    .strong { font-weight: 600; } .num { text-align: right; } .actions-col { width: 96px; text-align: right; }
    mat-paginator { border-top: 1px solid var(--lib-line); }
  `],
})
export class CategoriesListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(CategoriesStore);
  private readonly api = inject(CategoriesApiService);

  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly categories = this.store.categories;
  readonly meta = this.store.meta;
  readonly actionError = signal<string | null>(null);

  onSearch(q: string): void { this.store.patchQuery({ q }); }

  onPage(e: PageEvent): void { this.store.setPage(e.pageIndex + 1, e.pageSize); }

  load(): void { this.store.reload(); }

  openForm(category?: Category): void {
    this.dialog.open(CategoryFormDialog, { width: '440px', data: category ?? null })
      .afterClosed().subscribe(saved => {
        if (!saved) return;

        this.snack.open(category ? 'Category updated' : 'Category created', undefined, { duration: 4000 });
        this.store.reload();
      });
  }

  confirmDelete(c: Category): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: 'Delete «' + c.name + '»?',
      message: (c.books_count ?? 0) > 0
        ? 'This category holds ' + c.books_count + ' books: they need recategorising first.'
        : 'The category will be deleted.',
      confirmLabel: 'Delete category', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;

      this.actionError.set(null);
      this.api.remove(c.id).subscribe({
        next: () => {
          this.snack.open('Category deleted', undefined, { duration: 4000 });
          this.store.reload();
        },
        // 409 CATEGORY_IN_USE: books are still filed under it.
        error: (err: unknown) => this.actionError.set(businessMessage(err)),
      });
    });
  }
}
