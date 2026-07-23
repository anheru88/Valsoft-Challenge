import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar } from '@angular/material/snack-bar';
import { catchError, debounceTime, distinctUntilChanged, filter, forkJoin, of, switchMap } from 'rxjs';
import { businessMessage } from '../../../core/api/error-message';
import { Book, Loan, User } from '../../../core/models';
import { BooksApiService } from '../../books/data/books-api.service';
import { UsersApiService } from '../../users/data/users-api.service';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { DueStamp } from '../../../shared/ui/due-stamp';
import { AvailabilityBadge } from '../../../shared/ui/availability-badge';
import { LoansApiService } from '../data/loans-api.service';

const DAY = 86_400_000;

/** BR-LOAN-2. The server holds the real limit; this only stops a doomed submit. */
const LOAN_LIMIT = 5;

/** What the desk needs to know about the member beyond their name. */
type MemberStanding = User & { has_overdue?: boolean; active_titles?: number[] };

/**
 * The desk screen. Business rules are shown BEFORE the submit wherever the
 * client can already tell — the server has the last word (the `LOAN_*` codes).
 */
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
  private readonly api = inject(LoansApiService);
  private readonly usersApi = inject(UsersApiService);
  private readonly booksApi = inject(BooksApiService);

  readonly minDate = new Date(Date.now() + DAY);
  readonly maxDate = new Date(Date.now() + 60 * DAY);
  readonly loanLimit = LOAN_LIMIT;

  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly member = signal<MemberStanding | null>(null);
  readonly book = signal<Book | null>(null);

  readonly memberResults = signal<MemberStanding[]>([]);
  readonly bookResults = signal<Book[]>([]);

  readonly form = this.fb.group({
    memberQuery: [''],
    bookQuery: [''],
    dueDate: [new Date(Date.now() + 14 * DAY), Validators.required],
  });

  readonly memberOverdue = computed(() => this.member()?.has_overdue === true);
  readonly memberAtLimit = computed(() => (this.member()?.active_loans_count ?? 0) >= LOAN_LIMIT);
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
    this.watchMemberQuery();
    this.watchBookQuery();

    // Preselected from the book detail page: /loans/checkout?bookId=1
    const bookId = Number(this.route.snapshot.queryParamMap.get('bookId'));
    if (bookId) {
      this.booksApi.get(bookId).subscribe({ next: ({ data }) => this.book.set(data) });
    }
  }

  /**
   * Member lookup by name or email. `switchMap` is the point of doing this in
   * RxJS: at desk speed the answer to a half-typed name must be dropped, not
   * shown under the full one.
   */
  private watchMemberQuery(): void {
    this.form.controls.memberQuery.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      filter((value): value is string => typeof value === 'string' && value.trim().length >= 2),
      switchMap(value => this.usersApi.list({ q: value.trim(), role: 'member', per_page: 8 })
        .pipe(catchError(() => of({ data: [] as User[] })))),
      takeUntilDestroyed(),
    ).subscribe(page => this.memberResults.set(page.data));
  }

  /** Title or ISBN; a pasted barcode is an exact ISBN hit on the same endpoint. */
  private watchBookQuery(): void {
    this.form.controls.bookQuery.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      filter((value): value is string => typeof value === 'string' && value.trim().length >= 2),
      switchMap(value => this.booksApi.search({ q: value.trim(), per_page: 8 })
        .pipe(catchError(() => of({ data: [] as Book[] })))),
      takeUntilDestroyed(),
    ).subscribe(page => this.bookResults.set(page.data));
  }

  displayMember(m: User | string | null): string { return typeof m === 'object' && m ? m.name : String(m ?? ''); }
  displayBook(b: Book | string | null): string { return typeof b === 'object' && b ? b.title : String(b ?? ''); }

  /**
   * A member is picked, then their standing is fetched: the rules that block a
   * check-out (BR-LOAN-2, BR-LOAN-3, BR-LOAN-4) are about what they hold now,
   * and the list row only carries a count.
   */
  selectMember(m: MemberStanding): void {
    this.member.set(m);
    this.serverError.set(null);

    forkJoin({
      active: this.api.forUser(m.id, { status: 'active', per_page: 50 }),
      overdue: this.api.forUser(m.id, { status: 'overdue', per_page: 50 }),
    }).subscribe({
      next: ({ active, overdue }) => {
        const outstanding: Loan[] = [...active.data, ...overdue.data];

        this.member.set({
          ...m,
          active_loans_count: outstanding.length,
          has_overdue: overdue.data.length > 0,
          active_titles: outstanding.map(loan => loan.book.id),
        });
      },
      // Without the standing the client cannot pre-empt anything; the submit
      // still goes out and the server answers with the rule it broke.
      error: () => this.member.set(m),
    });
  }

  selectBook(b: Book): void {
    this.book.set(b);
    this.serverError.set(null);
  }

  submit(): void {
    if (!this.canSubmit()) return;
    this.serverError.set(null);
    this.saving.set(true);

    const book = this.book()!;
    const member = this.member()!;

    this.api.checkout({
      user_id: member.id,
      book_id: book.id,
      due_date: this.asDate(this.form.controls.dueDate.value),
    }).subscribe({
      next: () => {
        this.saving.set(false);
        this.snack.open('«' + book.title + '» lent to ' + member.name, undefined, { duration: 5000 });
        this.reset();
      },
      error: (err: unknown) => {
        this.saving.set(false);
        // The `LOAN_*` refusals name a rule, so they are stated inline next to
        // the form rather than thrown in a toast that scrolls away (PRD 5).
        this.serverError.set(businessMessage(err, 'Could not record the loan.'));
      },
    });
  }

  reset(): void {
    this.member.set(null); this.book.set(null); this.serverError.set(null);
    this.memberResults.set([]); this.bookResults.set([]);
    this.form.reset({ dueDate: new Date(Date.now() + 14 * DAY) });
  }

  goToLoans(): void { this.router.navigate(['/loans']); }

  /** The API takes a calendar day, not an instant. */
  private asDate(value: Date | null | undefined): string | undefined {
    if (!value) return undefined;

    const date = new Date(value);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
  }
}
