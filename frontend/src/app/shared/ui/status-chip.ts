import { Component, computed, input } from '@angular/core';
import { LoanStatus } from '../../core/models';

const META: Record<LoanStatus, { label: string; icon: string }> = {
  active:   { label: 'Active',   icon: '●' },
  overdue:  { label: 'Overdue',  icon: '▲' },
  returned: { label: 'Returned', icon: '✓' },
};

@Component({
  selector: 'lib-status-chip',
  standalone: true,
  template: `
    <span class="chip" [class]="status()">
      <span aria-hidden="true">{{ meta().icon }}</span> {{ meta().label }}
    </span>
  `,
  styles: [`
    .chip {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
    }
    .active   { background: var(--lib-green-soft); color: var(--lib-green-strong); }
    .overdue  { background: var(--lib-stamp-soft); color: var(--lib-stamp); }
    .returned { background: var(--lib-surface-2);  color: var(--lib-ink-soft); }
  `],
})
export class StatusChip {
  readonly status = input.required<LoanStatus>();
  readonly meta = computed(() => META[this.status()]);
}
