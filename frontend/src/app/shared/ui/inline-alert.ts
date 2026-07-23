import { Component, input } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';

/** Inline alert for business rules (LOAN_LIMIT_REACHED and friends).
 *  Los errores que bloquean un flujo nunca van en toast. */
@Component({
  selector: 'lib-inline-alert',
  standalone: true,
  imports: [MatIconModule],
  template: `
    <div class="alert" [class]="tone()" role="alert">
      <mat-icon aria-hidden="true">{{ tone() === 'warn' ? 'warning' : tone() === 'info' ? 'info' : 'block' }}</mat-icon>
      <div><ng-content /></div>
    </div>
  `,
  styles: [`
    .alert {
      display: flex; gap: var(--sp-3); align-items: flex-start;
      padding: var(--sp-3) var(--sp-4); border-radius: var(--lib-radius-sm);
      border: 1px solid currentColor; font-size: 13px;
    }
    .danger { background: var(--lib-stamp-soft); color: var(--lib-stamp); }
    .warn   { background: var(--lib-warn-soft);  color: var(--lib-warn); }
    .info   { background: var(--lib-info-soft);  color: var(--lib-info); }
  `],
})
export class InlineAlert {
  readonly tone = input<'danger' | 'warn' | 'info'>('danger');
}
