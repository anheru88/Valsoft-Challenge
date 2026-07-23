import { Component, computed, inject, signal } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatSnackBar } from '@angular/material/snack-bar';
import { forkJoin } from 'rxjs';
import { apiErrorCode, fieldErrors } from '../../../core/api/api-error';
import { businessMessage } from '../../../core/api/error-message';
import { Author, Book, Category } from '../../../core/models';
import { AuthorsApiService } from '../../authors/data/authors-api.service';
import { CategoriesApiService } from '../../categories/data/categories-api.service';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { BookPayload, BooksApiService } from '../data/books-api.service';

/** Client-side ISBN-10/13 checksum validation; the server validates again. */
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
  private readonly api = inject(BooksApiService);
  private readonly authorsApi = inject(AuthorsApiService);
  private readonly categoriesApi = inject(CategoriesApiService);

  private readonly bookId = Number(this.route.snapshot.paramMap.get('id')) || null;

  readonly currentYear = new Date().getFullYear();
  readonly isEdit = computed(() => this.bookId !== null);
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});
  readonly currentTitle = signal('');       // title loaded when editing
  readonly availableCopies = signal(0);     // read-only when editing

  readonly authors = signal<Pick<Author, 'id' | 'name'>[]>([]);
  readonly categories = signal<Pick<Category, 'id' | 'name'>[]>([]);

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

  constructor() {
    // The selects are useless half-filled, so the form waits for both lists —
    // and for the book itself when one is being edited.
    forkJoin({
      authors: this.authorsApi.list({ per_page: 100, sort: 'name' }),
      categories: this.categoriesApi.list({ per_page: 100, sort: 'name' }),
    }).subscribe({
      next: ({ authors, categories }) => {
        this.authors.set(authors.data);
        this.categories.set(categories.data);
        this.loading.set(false);
      },
      error: (err: unknown) => {
        this.loading.set(false);
        this.serverError.set(businessMessage(err, 'Could not load the authors and categories.'));
      },
    });

    if (this.bookId) {
      this.api.get(this.bookId).subscribe({
        next: ({ data }) => this.fill(data),
        error: (err: unknown) => this.serverError.set(businessMessage(err, 'Could not load this book.')),
      });
    }
  }

  private fill(book: Book): void {
    this.currentTitle.set(book.title);
    this.availableCopies.set(book.available_copies);

    this.form.patchValue({
      title: book.title,
      isbn: book.isbn,
      total_copies: book.total_copies,
      author_ids: book.authors.map(a => a.id),
      category_ids: book.categories.map(c => c.id),
      publisher: book.publisher ?? '',
      publication_year: book.publication_year ?? null,
      cover_url: book.cover_url ?? '',
      description: book.description ?? '',
    });
  }

  submit(): void {
    this.serverError.set(null);
    this.fieldErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);

    const value = this.form.getRawValue();
    const payload: BookPayload = {
      title: value.title,
      isbn: value.isbn,
      total_copies: value.total_copies,
      author_ids: value.author_ids,
      category_ids: value.category_ids,
      // Empty is not the same as "no value": the API takes null for the fields
      // that are optional, and rejects an empty string as a URL.
      publisher: value.publisher || null,
      publication_year: value.publication_year,
      cover_url: value.cover_url || null,
      description: value.description || null,
    };

    const request = this.bookId
      ? this.api.update(this.bookId, payload)
      : this.api.create(payload);

    request.subscribe({
      next: ({ data }) => {
        this.saving.set(false);
        this.snack.open(this.isEdit() ? 'Changes saved' : '«' + data.title + '» added to the catalogue',
                        undefined, { duration: 4000 });
        this.router.navigate(['/books', data.id]);
      },
      error: (err: unknown) => {
        this.saving.set(false);
        // A rejected field is answered next to that field. BOOK_COPIES_BELOW_LOANED
        // is a 422 about `total_copies` that carries no per-field list, so it is
        // placed by hand rather than shown as a page-wide failure.
        const perField = fieldErrors(err);

        if (apiErrorCode(err) === 'BOOK_COPIES_BELOW_LOANED') {
          perField['total_copies'] = businessMessage(err);
        }

        this.fieldErrors.set(perField);

        if (Object.keys(perField).length === 0) {
          this.serverError.set(businessMessage(err, 'Could not save the book.'));
        }
      },
    });
  }

  cancel(): void {
    this.router.navigate(this.bookId ? ['/books', this.bookId] : ['/books']);
  }
}
