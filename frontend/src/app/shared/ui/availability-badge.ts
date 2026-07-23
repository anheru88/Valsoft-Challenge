import { Component, computed, input } from '@angular/core';

/** "3 / 5 disponibles" — el estado nunca depende solo del color. */
@Component({
  selector: 'lib-availability-badge',
  standalone: true,
  template: `
    <span class="avail" [class.none]="available() === 0">
      <span class="dot" aria-hidden="true"></span>
      <span class="mono">{{ available() }}/{{ total() }}</span>
      <span class="label">{{ available() === 0 ? 'sin ejemplares' : 'disponibles' }}</span>
    </span>
  `,
  styles: [`
    .avail {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
      background: var(--lib-green-soft); color: var(--lib-green-strong);
    }
    .avail.none { background: var(--lib-stamp-soft); color: var(--lib-stamp); }
    .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .label { font-weight: 500; }
  `],
})
export class AvailabilityBadge {
  readonly available = input.required<number>();
  readonly total = input.required<number>();
}
