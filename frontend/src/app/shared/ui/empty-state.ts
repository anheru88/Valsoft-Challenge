import { Component, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

/** An empty state with direction: what happened, and what to do next. */
@Component({
  selector: 'lib-empty-state',
  standalone: true,
  imports: [MatButtonModule, MatIconModule],
  template: `
    <div class="empty" role="status">
      <mat-icon class="icon" aria-hidden="true">{{ icon() }}</mat-icon>
      <h3>{{ title() }}</h3>
      @if (message()) { <p class="muted">{{ message() }}</p> }
      @if (actionLabel()) {
        <button mat-flat-button color="primary" (click)="action.emit()">{{ actionLabel() }}</button>
      }
    </div>
  `,
  styles: [`
    .empty { display: grid; place-items: center; gap: var(--sp-2); text-align: center; padding: var(--sp-10) var(--sp-6); }
    .icon { font-size: 44px; width: 44px; height: 44px; color: var(--lib-ink-faint); }
    h3 { margin: 0; } p { margin: 0 0 var(--sp-3); max-width: 42ch; }
  `],
})
export class EmptyState {
  readonly icon = input('inbox');
  readonly title = input.required<string>();
  readonly message = input<string>();
  readonly actionLabel = input<string>();
  readonly action = output<void>();
}
