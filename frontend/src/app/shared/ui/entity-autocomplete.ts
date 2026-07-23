import { Component, DestroyRef, inject, input, output, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Observable, catchError, debounceTime, distinctUntilChanged, filter, of, switchMap, tap } from 'rxjs';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

/**
 * Look something up by typing part of its name.
 *
 * One component for every "find the member", "find the book", "find the author"
 * box in the product, because they are the same problem: debounce so the server
 * is not asked about every keystroke, and `switchMap` so the answer to a
 * half-typed word is dropped rather than shown under the finished one.
 *
 * The caller supplies the query and how to render a hit; nothing here knows
 * what an entity is.
 */
@Component({
  selector: 'lib-entity-autocomplete',
  standalone: true,
  imports: [ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatIconModule,
            MatAutocompleteModule, MatProgressSpinnerModule],
  template: `
    <mat-form-field appearance="outline" class="full">
      <mat-label>{{ label() }}</mat-label>
      <input matInput [formControl]="control" [matAutocomplete]="auto"
             [placeholder]="placeholder()" [class.mono-input]="mono()" />
      @if (loading()) {
        <mat-progress-spinner matSuffix mode="indeterminate" diameter="18" />
      } @else {
        <mat-icon matSuffix>{{ icon() }}</mat-icon>
      }
      @if (hint()) { <mat-hint>{{ hint() }}</mat-hint> }

      <mat-autocomplete #auto="matAutocomplete" (optionSelected)="pick($event.option.value)"
                        [displayWith]="displayFn">
        @for (item of results(); track primary(item)) {
          <mat-option [value]="item" [disabled]="isDisabled(item)">
            {{ primary(item) }}
            @if (secondary(item)) { <span class="faint">· {{ secondary(item) }}</span> }
          </mat-option>
        } @empty {
          @if (searched() && !loading()) {
            <mat-option disabled>{{ emptyLabel() }}</mat-option>
          }
        }
      </mat-autocomplete>
    </mat-form-field>
  `,
  styles: [`
    :host { display: block; }
    .full { width: 100%; }
    .mono-input { font-family: var(--lib-font-mono); }
  `],
})
export class EntityAutocomplete<T> {
  readonly label = input.required<string>();
  readonly placeholder = input('');
  readonly hint = input('');
  readonly icon = input('search');
  readonly mono = input(false);
  readonly emptyLabel = input('Nothing matches');
  readonly minLength = input(2);

  /** How to ask the server. Returns the hits for a term. */
  readonly search = input.required<(term: string) => Observable<T[]>>();
  /** The line the reader picks by. */
  readonly primaryText = input.required<(item: T) => string>();
  /** An optional second line — an email, an ISBN, a copy count. */
  readonly secondaryText = input<(item: T) => string>(() => '');
  /** Options that exist but cannot be chosen (a book with no copies free). */
  readonly disabledWhen = input<(item: T) => boolean>(() => false);

  readonly selected = output<T>();

  readonly control = new FormControl('', { nonNullable: true });
  readonly results = signal<T[]>([]);
  readonly loading = signal(false);
  /** True once a search has run, so "nothing matches" is not shown before one. */
  readonly searched = signal(false);

  private readonly destroyRef = inject(DestroyRef);

  constructor() {
    this.control.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      filter((value): value is string => typeof value === 'string'),
      tap(value => {
        if (value.trim().length < this.minLength()) {
          this.results.set([]);
          this.searched.set(false);
        }
      }),
      filter(value => value.trim().length >= this.minLength()),
      tap(() => this.loading.set(true)),
      switchMap(value => this.search()(value.trim()).pipe(catchError(() => of([] as T[])))),
      takeUntilDestroyed(this.destroyRef),
    ).subscribe(items => {
      this.results.set(items);
      this.loading.set(false);
      this.searched.set(true);
    });
  }

  /** Bound as a field so Material calls it with the component still as `this`. */
  readonly displayFn = (item: T | string | null): string =>
    item && typeof item === 'object' ? this.primaryText()(item) : String(item ?? '');

  primary(item: T): string { return this.primaryText()(item); }
  secondary(item: T): string { return this.secondaryText()(item); }
  isDisabled(item: T): boolean { return this.disabledWhen()(item); }

  pick(item: T): void { this.selected.emit(item); }

  /** Empties the box after a flow completes. */
  clear(): void {
    this.control.setValue('', { emitEvent: false });
    this.results.set([]);
    this.searched.set(false);
  }
}
