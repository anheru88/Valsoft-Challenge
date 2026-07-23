import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Author } from '../../core/models';
import { PageHeader } from '../../shared/ui/page-header';
import { SearchInput } from '../../shared/ui/search-input';
import { Skeleton } from '../../shared/ui/skeleton';
import { EmptyState } from '../../shared/ui/empty-state';
import { ConfirmDialog } from '../../shared/ui/confirm-dialog';
import { AuthorFormDialog } from './author-form-dialog';

@Component({
  selector: 'lib-authors-list-page',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, PageHeader, SearchInput, Skeleton, EmptyState],
  templateUrl: './authors-list-page.html',
  styles: [`.strong { font-weight: 600; } .num { text-align: right; } .actions-col { width: 96px; text-align: right; }`],
})
export class AuthorsListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);

  readonly loading = signal(false);

  // Demostración — GET /api/v1/authors?q&sort
  readonly authors = signal<Author[]>([
    { id: 3, name: 'Gabriel García Márquez', birth_year: 1927, books_count: 6 },
    { id: 5, name: 'Irene Vallejo', birth_year: 1979, books_count: 2 },
    { id: 8, name: 'Roald Dahl', birth_year: 1916, books_count: 9 },
  ]);

  onSearch(q: string): void { /* TODO: GET /authors?q= */ void q; }

  openForm(author?: Author): void {
    this.dialog.open(AuthorFormDialog, { width: '480px', data: author ?? null })
      .afterClosed().subscribe(changed => {
        if (changed) this.snack.open(author ? 'Autor actualizado' : 'Autor creado', undefined, { duration: 4000 });
      });
  }

  confirmDelete(a: Author): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: '¿Eliminar a ' + a.name + '?',
      message: (a.books_count ?? 0) > 0
        ? 'Este autor tiene ' + a.books_count + ' libros asociados: primero hay que reasignarlos.'
        : 'El autor se eliminará del catálogo.',
      confirmLabel: 'Eliminar autor', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;
      // TODO API: DELETE /authors/{id}; en 409 AUTHOR_IN_USE mostrar el motivo del servidor.
    });
  }
}
