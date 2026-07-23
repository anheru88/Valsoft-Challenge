import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Book, User } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';

const DAY = 86_400_000;

/** Pantalla de mostrador. Las validaciones de negocio se muestran ANTES de
 *  enviar cuando es posible; el servidor es la última palabra (códigos LOAN_*). */
@Component({
  selector: 'lib-checkout-page',
  standalone: true,
  imports: [ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatAutocompleteModule,
            MatDatepickerModule, MatNativeDateModule, MatButtonModule, MatIconModule,
            PageHeader, InlineAlert, DueStamp, AvailabilityBadge],
  templateUrl: './checkout-page.html',
  styleUrl: './checkout-page.scss',
})
export class CheckoutPage {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly snack = inject(MatSnackBar);

  readonly minDate = new Date(Date.now() + DAY);
  readonly maxDate = new Date(Date.now() + 60 * DAY);

  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly member = signal<(User & { has_overdue?: boolean; active_titles?: number[] }) | null>(null);
  readonly book = signal<Book | null>(null);

  // Resultados de autocompletar — sustituir por búsquedas al API con debounce
  readonly memberResults = signal<(User & { has_overdue?: boolean; active_titles?: number[] })[]>([
    { id: 34, name: 'Marta Ruiz', email: 'marta@example.com', roles: ['member'], permissions: [], is_active: true,
      active_loans_count: 2, has_overdue: false, active_titles: [7], created_at: '2025-01-10' },
    { id: 21, name: 'Lucía Gómez', email: 'lucia@example.com', roles: ['member'], permissions: [], is_active: true,
      active_loans_count: 3, has_overdue: true, active_titles: [2], created_at: '2024-11-02' },
  ]);
  readonly bookResults = signal<Book[]>([
    { id: 1, title: 'Cien años de soledad', isbn: '9780307474728', total_copies: 5, available_copies: 3,
      is_available: true, authors: [{ id: 3, name: 'G. García Márquez' }], categories: [], created_at: '' },
    { id: 2, title: 'El infinito en un junco', isbn: '9788417860790', total_copies: 2, available_copies: 0,
      is_available: false, authors: [{ id: 5, name: 'Irene Vallejo' }], categories: [], created_at: '' },
  ]);

  readonly form = this.fb.group({
    memberQuery: [''],
    bookQuery: [''],
    dueDate: [new Date(Date.now() + 14 * DAY), Validators.required],
  });

  readonly memberOverdue = computed(() => this.member()?.has_overdue === true);
  readonly memberAtLimit = computed(() => (this.member()?.active_loans_count ?? 0) >= 5);
  readonly memberBlocked = computed(() => this.memberOverdue() || this.memberAtLimit());
  readonly duplicateTitle = computed(() => {
    const m = this.member(); const b = this.book();
    return !!m && !!b && (m.active_titles ?? []).includes(b.id);
  });
  readonly canSubmit = computed(() =>
    !!this.member() && !!this.book() && !this.memberBlocked() && !this.duplicateTitle()
    && (this.book()?.available_copies ?? 0) > 0);
  readonly dueDateIso = computed(() => {
    const d = this.form.controls.dueDate.value;
    return d ? new Date(d).toISOString() : new Date().toISOString();
  });

  constructor() {
    // Preselección desde detalle de libro: /loans/checkout?bookId=1
    const bookId = Number(this.route.snapshot.queryParamMap.get('bookId'));
    if (bookId) {
      const found = this.bookResults().find(b => b.id === bookId);
      if (found) this.book.set(found);
      // TODO API: GET /books/{bookId} si no está en memoria
    }
  }

  displayMember(m: User | string | null): string { return typeof m === 'object' && m ? m.name : String(m ?? ''); }
  displayBook(b: Book | string | null): string { return typeof b === 'object' && b ? b.title : String(b ?? ''); }

  selectMember(m: User & { has_overdue?: boolean }): void {
    this.member.set(m);
    // TODO API: GET /users/{id} con contadores frescos (préstamos activos, vencidos, títulos)
  }
  selectBook(b: Book): void { this.book.set(b); }

  submit(): void {
    if (!this.canSubmit()) return;
    this.serverError.set(null);
    this.saving.set(true);
    // TODO API: POST /api/v1/loans { user_id, book_id, due_date }
    //  201 → snack '«Título» prestado a Nombre · devolver el d MMM' y reiniciar el formulario.
    //  409 → mapear code a mensaje en línea:
    //    LOAN_NO_COPIES        'No quedan ejemplares disponibles de este libro.'
    //    LOAN_LIMIT_REACHED    'El socio ya tiene 5 préstamos activos.'
    //    LOAN_MEMBER_OVERDUE   'El socio tiene préstamos vencidos.'
    //    LOAN_DUPLICATE_TITLE  'El socio ya tiene este título en préstamo.'
    const b = this.book()!; const m = this.member()!;
    this.snack.open('«' + b.title + '» prestado a ' + m.name, undefined, { duration: 5000 });
    this.saving.set(false);
    this.reset();
  }

  reset(): void {
    this.member.set(null); this.book.set(null); this.serverError.set(null);
    this.form.reset({ dueDate: new Date(Date.now() + 14 * DAY) });
  }
}
