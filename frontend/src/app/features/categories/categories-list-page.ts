import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Category } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { SearchInput } from '../../shared/ui/search-input';
import { EmptyState } from '../../shared/ui/empty-state';
import { ConfirmDialog } from '../../shared/ui/confirm-dialog';
import { CategoryFormDialog } from './category-form-dialog';

@Component({
  selector: 'lib-categories-list-page',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, PageHeader, SearchInput, EmptyState],
  templateUrl: './categories-list-page.html',
  styles: [`.strong { font-weight: 600; } .num { text-align: right; } .actions-col { width: 96px; text-align: right; }`],
})
export class CategoriesListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);

  // Demo data — GET /api/v1/categories
  readonly categories = signal<Category[]>([
    { id: 1, name: 'Fiction', slug: 'fiction', books_count: 412 },
    { id: 2, name: 'Historia', slug: 'historia', books_count: 268 },
    { id: 3, name: 'Infantil', slug: 'infantil', books_count: 231 },
  ]);

  onSearch(q: string): void { void q; /* TODO: GET /categories?q= */ }

  openForm(category?: Category): void {
    this.dialog.open(CategoryFormDialog, { width: '440px', data: category ?? null })
      .afterClosed().subscribe(changed => {
        if (changed) this.snack.open(category ? 'Category actualizada' : 'Category creada', undefined, { duration: 4000 });
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
      // TODO API: DELETE /categories/{id}; en 409 CATEGORY_IN_USE mostrar motivo.
    });
  }
}
