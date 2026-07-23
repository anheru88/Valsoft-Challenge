import { Component, computed, input } from '@angular/core';
import { DatePipe, UpperCasePipe } from '@angular/common';
import { LoanStatus } from '../../core/models';

/**
 * A due-date stamp in the style of a loan card: monospaced, uppercase, inked
 * border and a slight rotation. The signature element of the system.
 *  - active   → green "DUE 24 JUL"
 *  - overdue  → red   "OVERDUE · 3 DAYS"
 *  - returned → grey  "RETURNED 12 JUL"
 */
@Component({
  selector: 'lib-due-stamp',
  standalone: true,
  imports: [DatePipe, UpperCasePipe],
  template: `
    <span class="stamp" [class]="status()" role="status">
      @switch (status()) {
        @case ('overdue')  { OVERDUE · {{ daysOverdue() }} {{ daysOverdue() === 1 ? 'DAY' : 'DAYS' }} }
        @case ('returned') { RETURNED {{ (returnedAt() ?? dueDate()) | date: 'd MMM' | uppercase }} }
        @default           { DUE {{ dueDate() | date: 'd MMM' | uppercase }} }
      }
    </span>
  `,
  styles: [`
    .stamp {
      display: inline-block;
      font-family: var(--lib-font-mono);
      font-size: 11px; font-weight: 600; letter-spacing: .08em;
      padding: 3px 9px;
      border: 1.5px solid currentColor; border-radius: 6px;
      transform: rotate(-1.5deg);
      background: var(--lib-surface);
    }
    .active   { color: var(--lib-green-strong); }
    .overdue  { color: var(--lib-stamp); background: var(--lib-stamp-soft); }
    .returned { color: var(--lib-ink-faint); transform: none; border-style: dashed; }
    @media (prefers-reduced-motion: reduce) { .stamp { transform: none; } }
  `],
})
export class DueStamp {
  readonly status = input.required<LoanStatus>();
  readonly dueDate = input.required<string>();
  readonly returnedAt = input<string | null>(null);
  readonly daysOverdue = input<number>(0);
}
