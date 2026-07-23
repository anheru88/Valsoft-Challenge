import { Component, computed, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthStore } from '../../../core/auth.store';
import { Book, Category } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { ConfirmDialog } from '../../../shared/ui/confirm-dialog';

interface BookFilters { q: string; categoryId: number | null; onlyAvailable: boolean; }

@Component({
  selector: 'lib-books-list-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatFormFieldModule,
            MatSelectModule, MatSlideToggleModule, MatPaginatorModule,
            PageHeader, SearchInput, AvailabilityBadge, Skeleton, EmptyState, ErrorState],
  templateUrl: './books-list-page.html',
  styleUrl: './books-list-page.scss',
})
export class BooksListPage {
  private readonly auth = inject(AuthStore);
  private readonly router = inject(Router);
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);

  readonly isStaff = this.auth.isStaff;
  readonly loading = signal(false);
  readonly error = signal(false);

  readonly filters = signal<BookFilters>({ q: '', categoryId: null, onlyAvailable: false });
  readonly sort = signal<{ field: string; dir: 'asc' | 'desc' }>({ field: 'title', dir: 'asc' });
  readonly meta = signal({ current_page: 1, per_page: 15, total: 3, last_page: 1 });

  readonly hasActiveFilters = computed(() =>
    this.filters().q !== '' || this.filters().categoryId !== null || this.filters().onlyAvailable);
  readonly totalLabel = computed(() => this.meta().total + ' títulos en catálogo');

  // Datos de demostración — sustituir por GET /api/v1/books con filtros/orden/página
  readonly categories = signal<Pick<Category, 'id' | 'name'>[]>([
    { id: 1, name: 'Narrativa' }, { id: 2, name: 'Historia' }, { id: 3, name: 'Infantil' },
  ]);

  readonly books = signal<Book[]>([
    { id: 1, title: 'Cien años de soledad', isbn: '9780307474728', publisher: 'Vintage', publication_year: 1967,
      total_copies: 5, available_copies: 3, is_available: true, created_at: '2026-06-01',
      authors: [{ id: 3, name: 'Gabriel García Márquez' }], categories: [{ id: 1, name: 'Narrativa', slug: 'narrativa' }] },
    { id: 2, title: 'El infinito en un junco', isbn: '9788417860790', publisher: 'Siruela', publication_year: 2019,
      total_copies: 2, available_copies: 0, is_available: false, created_at: '2026-06-10',
      authors: [{ id: 5, name: 'Irene Vallejo' }], categories: [{ id: 2, name: 'Historia', slug: 'historia' }] },
    { id: 3, title: 'Matilda', isbn: '9788420482880', publisher: 'Alfaguara', publication_year: 1988,
      total_copies: 4, available_copies: 4, is_available: true, created_at: '2026-06-15',
      authors: [{ id: 8, name: 'Roald Dahl' }], categories: [{ id: 3, name: 'Infantil', slug: 'infantil' }] },
  ]);

  authorNames(b: Book): string { return b.authors.map(a => a.name).join(', '); }

  setFilter(patch: Partial<BookFilters>): void {
    this.filters.update(f => ({ ...f, ...patch }));
    this.load(); // TODO: sincronizar también con queryParams del router
  }

  clearFilters(): void {
    this.filters.set({ q: '', categoryId: null, onlyAvailable: false });
    this.load();
  }

  toggleSort(field: string): void {
    this.sort.update(s => s.field === field
      ? { field, dir: s.dir === 'asc' ? 'desc' : 'asc' }
      : { field, dir: 'asc' });
    this.load();
  }
  sortIndicator(field: string): string {
    const s = this.sort();
    return s.field !== field ? '' : s.dir === 'asc' ? '↑' : '↓';
  }

  onPage(e: PageEvent): void {
    this.meta.update(m => ({ ...m, current_page: e.pageIndex + 1, per_page: e.pageSize }));
    this.load();
  }

  goNew(): void { this.router.navigate(['/books/new']); }

  confirmDelete(b: Book): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: '¿Eliminar «' + b.title + '»?',
      message: 'El libro se retirará del catálogo. Los préstamos ya devueltos conservan su historial.',
      confirmLabel: 'Eliminar libro', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;
      // TODO API: DELETE /api/v1/books/{id}
      // 204 → this.snack.open('Libro eliminado', undefined, { duration: 4000 }); this.load();
      // 409 BOOK_HAS_ACTIVE_LOANS → diálogo/alerta con el motivo y enlace a los préstamos activos.
      this.snack.open('Libro eliminado', undefined, { duration: 4000 });
    });
  }

  load(): void {
    // TODO API: GET /api/v1/books?q&category_id&available&sort&direction&page&per_page
  }
}
