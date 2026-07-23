import { Component, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

/** A failed load with a retry; the trace_id stays folded away for support. */
@Component({
  selector: 'lib-error-state',
  standalone: true,
  imports: [MatButtonModule, MatIconModule],
  template: `
    <div class="error card" role="alert">
      <mat-icon aria-hidden="true">error_outline</mat-icon>
      <div class="grow">
        <strong>{{ title() }}</strong>
        <p class="muted">{{ message() }}</p>
        @if (traceId()) {
          <details><summary class="faint">Technical details</summary>
            <code class="mono">trace_id: {{ traceId() }}</code>
          </details>
        }
      </div>
      <button mat-stroked-button (click)="retry.emit()">Try again</button>
    </div>
  `,
  styles: [`
    .error { display: flex; gap: var(--sp-4); align-items: flex-start; padding: var(--sp-5); border-left: 3px solid var(--lib-stamp); }
    mat-icon { color: var(--lib-stamp); }
    p { margin: 2px 0; } details { margin-top: var(--sp-2); }
  `],
})
export class ErrorState {
  readonly title = input('Could not load');
  readonly message = input('Check your connection and try again.');
  readonly traceId = input<string>();
  readonly retry = output<void>();
}
