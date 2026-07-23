import { Component, computed, inject, signal } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatSnackBar } from '@angular/material/snack-bar';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';

/** Validación de checksum ISBN-10/13 en cliente (el servidor revalida). */
export function isbnValidator(control: AbstractControl): ValidationErrors | null {
  const raw = String(control.value ?? '').replace(/[-\s]/g, '');
  if (!raw) return null;
  if (/^\d{9}[\dX]$/i.test(raw)) {
    const sum = raw.split('').reduce((acc, ch, i) =>
      acc + (ch.toUpperCase() === 'X' ? 10 : Number(ch)) * (10 - i), 0);
    return sum % 11 === 0 ? null : { isbn: true };
  }
  if (/^\d{13}$/.test(raw)) {
    const sum = raw.split('').reduce((acc, ch, i) => acc + Number(ch) * (i % 2 === 0 ? 1 : 3), 0);
    return sum % 10 === 0 ? null : { isbn: true };
  }
  return { isbn: true };
}

@Component({
  selector: 'lib-book-form-page',
  standalone: true,
  imports: [ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatSelectModule,
            MatButtonModule, PageHeader, InlineAlert],
  templateUrl: './book-form-page.html',
  styles: [`.narrow { max-width: 860px; } lib-inline-alert { display: block; margin-bottom: var(--sp-4); }`],
})
export class BookFormPage {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly snack = inject(MatSnackBar);

  readonly currentYear = new Date().getFullYear();
  readonly isEdit = computed(() => this.route.snapshot.paramMap.has('id'));
  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});
  readonly currentTitle = signal('');       // título cargado en modo edición
  readonly availableCopies = signal(0);     // solo lectura en edición

  // Catálogos para selects — sustituir por GET /authors y GET /categories
  readonly authors = signal([{ id: 3, name: 'Gabriel García Márquez' }, { id: 5, name: 'Irene Vallejo' }, { id: 8, name: 'Roald Dahl' }]);
  readonly categories = signal([{ id: 1, name: 'Narrativa' }, { id: 2, name: 'Historia' }, { id: 3, name: 'Infantil' }]);

  readonly form = this.fb.nonNullable.group({
    title: ['', Validators.required],
    isbn: ['', [Validators.required, isbnValidator]],
    total_copies: [1, [Validators.required, Validators.min(1)]],
    author_ids: [[] as number[], Validators.required],
    category_ids: [[] as number[], Validators.required],
    publisher: [''],
    publication_year: [null as number | null],
    cover_url: [''],
    description: [''],
  });

  submit(): void {
    this.serverError.set(null);
    this.fieldErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    // TODO API: POST /api/v1/books | PUT /api/v1/books/{id}
    //  201/200 → snack '{isEdit ? "Cambios guardados" : "Libro creado"}' y volver al detalle.
    //  422 VALIDATION_FAILED → fieldErrors por campo (isbn duplicado, etc.)
    //  422 BOOK_COPIES_BELOW_LOANED → fieldErrors['total_copies'] con el mensaje del API.
    this.saving.set(false);
    this.snack.open(this.isEdit() ? 'Cambios guardados' : 'Libro creado', undefined, { duration: 4000 });
    this.router.navigate(['/books']);
  }

  cancel(): void { this.router.navigate(['/books']); }
}
