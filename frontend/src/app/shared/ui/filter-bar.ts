import { Component, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';

/**
 * The row of controls that narrows a list.
 *
 * Thin on purpose — the controls differ per screen, the behaviour around them
 * does not: it is a search landmark, it wraps on a narrow screen, and it offers
 * a way out only once something is actually filtering. Showing "Clear filters"
 * on an untouched list teaches people to ignore it.
 */
@Component({
  selector: 'lib-filter-bar',
  standalone: true,
  imports: [MatButtonModule],
  template: `
    <div class="toolbar" role="search" [attr.aria-label]="label()">
      <ng-content />
      @if (active()) {
        <button mat-button (click)="cleared.emit()">{{ clearLabel() }}</button>
      }
    </div>
  `,
  styles: [`
    :host { display: block; }
    .toolbar { display: flex; flex-wrap: wrap; gap: var(--sp-3); align-items: center; margin-bottom: var(--sp-4); }
    @media (max-width: 599px) {
      .toolbar > * { flex: 1 1 100%; }
    }
  `],
})
export class FilterBar {
  readonly label = input('Filters');
  readonly active = input(false);
  readonly clearLabel = input('Clear filters');
  readonly cleared = output<void>();
}
