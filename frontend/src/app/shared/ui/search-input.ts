import { Component, DestroyRef, OnInit, inject, input, output } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { debounceTime, distinctUntilChanged } from 'rxjs';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';

/** Debounced search for the filter bars of list screens. */
@Component({
  selector: 'lib-search-input',
  standalone: true,
  imports: [ReactiveFormsModule, MatIconModule, MatButtonModule],
  template: `
    <div class="wrap">
      <mat-icon aria-hidden="true">search</mat-icon>
      <input type="search" [formControl]="control"
             [placeholder]="placeholder()" [attr.aria-label]="placeholder()" />
      @if (control.value) {
        <button mat-icon-button (click)="control.setValue('')" aria-label="Clear the search">
          <mat-icon>close</mat-icon>
        </button>
      }
    </div>
  `,
  styles: [`
    .wrap {
      display: flex; align-items: center; gap: var(--sp-2);
      padding: 4px 6px 4px 12px; min-width: 260px;
      border: 1px solid var(--lib-line); border-radius: 999px;
      background: var(--lib-surface); color: var(--lib-ink-soft);
    }
    input { flex: 1; border: 0; background: transparent; color: var(--lib-ink); outline: none; height: 30px; }
  `],
})
export class SearchInput implements OnInit {
  readonly placeholder = input('Search…');
  readonly initial = input('');
  readonly changed = output<string>();

  readonly control = new FormControl('', { nonNullable: true });
  private readonly destroyRef = inject(DestroyRef);

  ngOnInit(): void {
    this.control.setValue(this.initial(), { emitEvent: false });
    this.control.valueChanges
      .pipe(debounceTime(300), distinctUntilChanged(), takeUntilDestroyed(this.destroyRef))
      .subscribe(v => this.changed.emit(v.trim()));
  }
}
