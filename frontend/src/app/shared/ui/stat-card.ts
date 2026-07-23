import { Component, input } from '@angular/core';
import { DecimalPipe } from '@angular/common';

@Component({
  selector: 'lib-stat-card',
  standalone: true,
  imports: [DecimalPipe],
  template: `
    <div class="stat card" [class.alert]="tone() === 'alert'">
      <span class="label">{{ label() }}</span>
      <span class="value">{{ value() | number }}</span>
      @if (hint()) { <span class="hint faint">{{ hint() }}</span> }
    </div>
  `,
  styles: [`
    .stat { display: flex; flex-direction: column; gap: 2px; padding: var(--sp-4) var(--sp-5); }
    .label { font-size: 12px; text-transform: uppercase; letter-spacing: .07em; color: var(--lib-ink-soft); }
    .value { font-family: var(--lib-font-display); font-size: 32px; line-height: 1.15; color: var(--lib-ink); }
    .hint { font-size: 12px; }
    .alert { border-left: 3px solid var(--lib-stamp); }
    .alert .value { color: var(--lib-stamp); }
  `],
})
export class StatCard {
  readonly label = input.required<string>();
  readonly value = input.required<number>();
  readonly hint = input<string>();
  readonly tone = input<'default' | 'alert'>('default');
}
